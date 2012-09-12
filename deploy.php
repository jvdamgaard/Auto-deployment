<?php

/**
 *  Github automatic deployment script for php projects
 *
 *  This file will make your project available on a live server several seconds
 *      after each push was made to a repository by any user
 */

// Prevent some childish-hackish things
if(!isset($_POST['payload']) || empty($_POST['payload'])) {
    GitHubAutoDeployment::log('error', 'No payload content', true);
}

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
    const LOG_FILE = './log.txt';

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
    public $ex_files = array();

    /**
     *  List of folders you want to exclude from a deploy
     *  All files in that folders will be ignored and not deployed too
     *  This path should be relative to a GH_UPLOAD_PATH, without facing and trailing slashes
     */
    public $ex_dirs  = array();

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
            $add    = array_merge($commit->added,$commit->modified);

            foreach($add as $filename) {
                if (!addFile($filename)) {
                    GitHubAutoDeployment::log('error', 'Error while trying to upload this file: ' . $filename);
                    $errors = true;
                }
            }

            foreach($commit->removed as $filename) {
                if (!removeFile($filename)) {
                    GitHubAutoDeployment::log('error', 'Error while trying to remove this file: ' . $filename);
                    $errors = true;
                }
            }
        }
        if (!$errors) {
            GitHubAutoDeployment::log('deployment', 'All systems go!');
        }
    }

    protected function addFile($file) {

            if ($this->excluding_file($modify)) continue;

            $url  = 'https://raw.github.com/' . GH_USERNAME . '/' . GH_REPO . '/' . GH_BRANCH . '/' . $file;
            $path = GH_UPLOAD_PATH . '/' . $file;
            $this->create_dir($path);

            $content = file_get_contents($url);

            // upload
            if(file_put_contents($path, $content)) {
                return true;
            } else {
                return false;
            }
    }

    protected function removeFile($file) {

            $path = GH_UPLOAD_PATH . '/' . $file;

            // upload
            if(unlink($path)) {
                return true;
            } else {
                return false;
            }
    }

    /**
     *  Check that current file is not in an exlcude list
     *      If returned true - omit.
     */
    protected function excluding_file($file){
        if (in_array($file, $this->ex_files) || in_array(dirname($file), $this->ex_dirs))
            return true;

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
    protected function create_dir($file){
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