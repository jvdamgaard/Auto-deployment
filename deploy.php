<?php

/**
 *  Github automatic deployment script for php projects
 *
 *  This file will make your project available on a live server several seconds
 *      after each push was made to a repository by any user
 *
 *  TO-DO
 *      x ignore files (git files)
 *      - compile less
 *      x minify js and css with YUI compressor
 *      - minify html
 *      - minify images
 *      - refresh build option
 *      - options for class
 *      - class in seperate file
 */

// Prevent some childish-hackish things
if(!isset($_POST['payload']) || empty($_POST['payload'])) {
    GitHubAutoDeployment::log('error', 'No payload content', true);
}

include_once('yuicompressor.php');

/**
 *  Below are configs used for a deploy. Double check them.
 */
// Your github username
if (!defined('GH_USERNAME'))
    define('GH_USERNAME', 'jvdamgaard');
// Slug of the epo you want to autodeploy
if (!defined('GH_REPO'))
    define('GH_REPO', 'm.adaptivedesignstudio.com');
// What branch should we take care of? Only one can be used
if (!defined('GH_BRANCH'))
    define('GH_BRANCH', 'master');
// Where you want to deploy the github project files. No trailing slash
if (!defined('GH_UPLOAD_PATH'))
    define('GH_UPLOAD_PATH', dirname(__FILE__) . '');

/**
 *  Main class itself where all the magic happens
 */
class GitHubAutoDeployment {
    // where to save all deploy results
    const LOG_FILE = './deploy_log.txt';

    // what we received from Github
    public $data   = false;

    // list of files to process on a server
    public $files  = array();

    // list of allowed IPs for a deploy. Defaults are Github IPs
    public $ips    = array(
                        '207.97.227.253',
                        '50.57.128.197',
                        '108.171.174.178'
                    );

    /**
     *  List of files you want to exclude from a deploy
     *  This path should be relative to a GH_UPLOAD_PATH, without facing and trailing slashes
     */
    public $ex_files = array(
							'*.gitattributes',
							'*.gitignore',
							'*.md',
							'*.less'
						);

    /**
     *  List of folders you want to exclude from a deploy
     *  All files in that folders will be ignored and not deployed too
     *  This path should be relative to a GH_UPLOAD_PATH, without facing and trailing slashes
     */
    public $ex_dirs  = array(
    						'less'
    					);


    public $cssCompressor = new YUICompressor(
                                dirname(__FILE__).'/yuicompressor-2.4.7.jar',
                                dirname(__FILE__),
                                array('type' => 'css')
                            );
    public $jsCompressor =  new YUICompressor(
                                dirname(__FILE__).'/yuicompressor-2.4.7.jar',
                                dirname(__FILE__),
                                array('type' => 'js')
                            );

    /**
     *  Now time for a deploy - get the POST data
     */
    function __construct($payload){

        // check that we have rights to deploy - IP check
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->ips)) {
            GitHubAutoDeployment::log('error', 'Attempt to make a deploy from a not allowed IP: ' . $_SERVER['REMOTE_ADDR'], true);
        }

        // We received json object - decode it
        $this->data = json_decode(stripslashes($payload));

        // different branch
        if($this->data->ref !== 'refs/heads/'.GH_BRANCH) {
            die;
        }

        // if commit data is empty - exit
        if(empty($this->data->commits) || !is_array($this->data->commits)) {
            GitHubAutoDeployment::log('error', 'Commits data is empty (no commits?)', true);
        }

        // the main deploy itself
        $this->deploy();
    }

    /**
     *  Actually the deploy is done below
     */
    protected function deploy(){

        $errors = false;

        // get the list of all files we need to upload
        foreach($this->data->commits as $commit){
            $add = array_merge($commit->added,$commit->modified);

            foreach($add as $filename) {
                if (!$this->excludeFile($filename)) {
                    if (!$this->addFile($filename)) {
                        GitHubAutoDeployment::log('error', 'Error while trying to upload this file: ' . $filename);
                        $errors = true;
                    }
                }
            }

            foreach($commit->removed as $filename) {
                if (!$this->excludeFile($filename)) {
                    if (!$this->removeFile($filename)) {
                        GitHubAutoDeployment::log('error', 'Error while trying to remove this file: ' . $filename);
                        $errors = true;
                    }
                }
            }
        }
        if (!$errors) {
            GitHubAutoDeployment::log('deployment', 'All systems go!');
        }
    }

    protected function addFile($file) {

            $url  = 'https://raw.github.com/' . GH_USERNAME . '/' . GH_REPO . '/' . GH_BRANCH . '/' . $file;
            $path = GH_UPLOAD_PATH . '/' . $file;
            $this->createDir($path);

            $content = file_get_contents($url);

            // upload
            return file_put_contents($path, $content);
    }

    protected function removeFile($file) {

            $path = GH_UPLOAD_PATH . '/' . $file;

            // delete
            return unlink($path);
    }

    /**
     *  Check that current file is not in an exlcude list
     *      If returned true - omit.
     */
    protected function excludeFile($file){

		$file_info = pathinfo($file);

		// check file
		foreach ($this->ex_files as $ex_file) {
			
			// file type
			if (strpos('*.', $ex_file, 0) === 0) {
				$extension = substr($ex_file,2);
				if ($extension == $file_info['extension']) {
					return true;
				}
						
			// file	
			} elseif (strpos('*', $ex_file, 0) === 0) {
				$filename = substr($ex_file,1);
				if ($filename == $file_info['filename']) {
					return true;
				}
			
			// file with path
			} elseif ($file == $ex_file) {
				return true;
			}
		}
		
		// check dir
		foreach ($this->ex_dirs as $ex_dir) {
			if (strpos($ex_dir, $file_info['dirname'], 0) === 0) {
				return true;
			}
		}

		return false;
    }

    /**
     *  Save to the log all events connected with the deployment process
     */
    static function log($status, $message, $die = false){
        file_put_contents(GitHubAutoDeployment::LOG_FILE,
                            date('Y.m.d@H:i:s') . ' - ' . strtoupper($status) . ' - ' . $message . "\n",
                            FILE_APPEND
                        );
        if ($die)
            die;
    }

    /**
     *  Create appropriate folders if they don't exist
     */
    protected function createDir($file){
        $path = dirname($file);
        if(is_dir($path))
            return;

        // recursion
        if (!mkdir($path, 0755, true)) {
            GitHubAutoDeployment::log('error', 'Failed to create folders: ' . $path, true);
        }
    }

}

$deploy = new GitHubAutoDeployment($_POST['payload']);

?>