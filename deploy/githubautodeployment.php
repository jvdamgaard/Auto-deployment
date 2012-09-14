<?php
/**
 *  Main class itself where all the magic happens
 */

include_once('deploy/yuicompressor.php');

class GitHubAutoDeployment {

    const   LOG_FILE        = './deploy/log.txt';       // where to save all deploy results

    public  $data           = false;                    // what we received from Github
    public  $branch         = false;					// Actual branch to deploy from
    public  $settings       = array(
                                'payload' => null,
                                'username' => null,		// GitHub username
                                'repository' => null,			// repository on GitHub
                                'branches' => null,		// array - key: branch (* could be used alone or in beginnng or end of string); val: absolute path to deploye branch in
                                'compileLess' => true,
                                'compressJs' => true,
                                'compressCss' => true,
                                'compressHtml' => true,
                                'compressImages' => true
                            )

    public  $files          = array();                  // list of files to process on a server
    public  $ips            = array(                    // list of allowed IPs for a deploy. Defaults are Github IPs
                                '207.97.227.253',
                                '50.57.128.197',
                                '108.171.174.178'
                            );
    public  $ex_files       = array(                    // list of files you want to exclude from a deploy
                                '*.gitattributes',
                                '*.gitignore',
                                '*.md',
                                '*.less'
                            );
    public  $ex_dirs        = array(                    // list of folders you want to exclude from a deploy
                                'less'
                            );
    public  $cssCompressor  = new YUICompressor(		// css compresssor
                                dirname(__FILE__).'/deploy/yuicompressor-2.4.7.jar',
                                dirname(__FILE__),
                                array('type' => 'css')
                            );
    public 	$jsCompressor   = new YUICompressor(		// js compressor
                                dirname(__FILE__).'/deploy/yuicompressor-2.4.7.jar',
                                dirname(__FILE__),
                                array('type' => 'js')
                            );

    /**
     *  Now time for a deploy - get the POST data
     */
    function __construct($settings){

        foreach ($settings as $key->$val) {
            $this->settings[$key] = $val;
        }

        // check that we have rights to deploy - IP check
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->ips)) {
            GitHubAutoDeployment::log('error', 'Attempt to make a deploy from a not allowed IP: ' . $_SERVER['REMOTE_ADDR'], true);
        }

        // We received json object - decode it
        $this->data = json_decode(stripslashes($this->settings['payload']));

        // debug
        GitHubAutoDeployment::log('settings 1', print_r($this->settings,true), false);

        // If branch is not specified
        if(!$this->testBranch()) {
            die;
        }

        // debug
        GitHubAutoDeployment::log('settings 2', print_r($this->settings,true), false);

		$this->deploy();
    }

    protected function testBranch() {
        if(!isset($this->settings['branches']) || empty($this->settings['branches'])) {
            return false;
        }
        $this->branch = substr($this->data-ref,strrchr($this->data-ref,"/")+1);

        // TO-DO: asterix search
        if (!array_key_exists($this->branch, $this->settings['branches'])) {
            return false;
        }

		return true;
        // to-do: find branch
    }

    /**
     *  Actually the deploy is done below
     */
    public function deploy(){

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

            $url  = 'https://raw.github.com/' . $this->settings['username'] . '/' . $this->settings['repository'] . '/' . $this->branch . '/' . $file;
            $path = $this->settings['branches'][$this->branch] . '/' . $file;
            $this->createDir($path);

            $content = file_get_contents($url);

            // upload
            return file_put_contents($path, $content);
    }

    protected function removeFile($file) {

            $path = $this->settings['branches'][$this->branch] . '/' . $file;

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
?>