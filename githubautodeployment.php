<?php
/**
 *  Main class itself where all the magic happens
 */

class GitHubAutoDeployment {

    const   LOG_FILE        = './log.txt';       // where to save all deploy results

    public  $data           = false;                    // what we received from Github
    public  $branch         = false;					// Actual branch to deploy from
    public  $branchPath     = false;                    // Actual branch to deploy from
    public  $settings       = array(
                                'payload' => null,
                                'username' => null,		// GitHub username
                                'repository' => null,	// repository on GitHub
                                'branches' => null,		// array - key: branch (* could be used alone or in beginnng or end of string); val: absolute path to deploye branch in
                                'excludeFiles' => array(    // list of files you want to exclude from a deploy
                                    '*.gitattributes',
                                    '*.gitignore',
                                    '*.md'
                                ),
                                'excludeDirs' => array(      // list of folders you want to exclude from a deploy
                                    
                                ),
		                        'folder' => 'publish',
								'email' => ''
                            );

    public  $files          = array();                  // list of files to process on a server
    public  $ips            = array(                    // list of allowed IPs for a deploy. Defaults are Github IPs
                                '207.97.227.253',
                                '50.57.128.197',
                                '108.171.174.178'
                            );
	public $emailLog		= '';
	public $startTime		= null;

    /**
     *  Now time for a deploy - get the POST data
     */
    function __construct($settings){
	
		$this->startTime = time();

        foreach ($settings as $key => $val) {
            if ($key == 'ip') {
                array_push($this->ips, $val);
            } else {
                $this->settings[$key] = $val;
            }
        }

		$this->emailLog .= "Deploy from ".$this->settings['repository']." by ".$this->settings['username'];
		$this->emailLog .= "\n".date('d.m.Y @ H:i:s', $this->startTime).": Deploy startet\n";

        // check that we have rights to deploy - IP check
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->ips)) {
            GitHubAutoDeployment::log('error', 'Not allowed IP: ' . $_SERVER['REMOTE_ADDR'], true);
        }

        // We received json object - decode it
        $this->data = json_decode(stripslashes($this->settings['payload']));
		$this->emailLog .= "\n".date('d.m.Y @ H:i:s').": Payload decoded";

        // If branch is not specified
        if(!$this->testBranch()) {
			GitHubAutoDeployment::log('error', 'Not allowed branch', true);
        }
		$this->emailLog .= "\n".date('d.m.Y @ H:i:s').": Branch approved - ".$this->branch;
		$this->emailLog .= "\n".date('d.m.Y @ H:i:s').": Branch dir - ".$this->branchPath;

		$this->deploy();
    }

    protected function testBranch() {
        if(!isset($this->settings['branches']) || empty($this->settings['branches'])) {
            return false;
        }

        $this->branch = substr(strrchr($this->data->ref,"/"),1);

        // Is equal to
        if (array_key_exists($this->branch, $this->settings['branches'])) {
            $this->branchPath = $this->settings['branches'][$this->branch];
            return true;

        // Branches with asterix
        } else {
            foreach ($this->settings['branches'] as $branch => $dir) {

                // Starts with asterix
                if ($this->startsWith($branch, '*')) {
                    $tempBranch = substr($branch, 1);
                    if ($this->endsWith($this->branch,$tempBranch)) {
                        $this->branch = $branch;
                        $this->branchPath = $dir;
                        return true;
                    }

                // Ends with asterix
                } elseif ($this->endsWith($branch, '*')) {
                    $tempBranch = substr($branch, 0,-1);
                    if ($this->startsWith($this->branch,$tempBranch)) {
                        $this->branch = $branch;
                        $this->branchPath = $dir;
                        return true;
                    }
                }
            }
        } 

        // Branch wasn't found in settings
		return false;
    }

    protected function endsWith($str,$test) {
        return (substr_compare($str, $test, -strlen($test), strlen($test)) === 0);
    }

    protected function startsWith($str,$test) {
        return (strpos($str, $test) === 0);
    }

    /**
     *  Actually the deploy is done below
     */
    public function deploy(){

        $errors = false;

        // get the list of all files we need to upload
        foreach($this->data->commits as $commit){
	
			$this->emailLog .= "\n";
            $add = array_merge($commit->added,$commit->modified);

            foreach($add as $filename) {
                if (!$this->excludeFile($filename)) {
                    if (!$this->addFile($filename)) {
                        GitHubAutoDeployment::log('error', 'Error while trying to upload file: ' . setCorrectFilePath($filename));
						$this->emailLog .= "\n".date('d.m.Y @ H:i:s').": !!! ERROR !!! added \t".$this->setCorrectFilePath($filename);
                        $errors = true;
                    } else {
						$this->emailLog .= "\n".date('d.m.Y @ H:i:s').": added \t".$this->setCorrectFilePath($filename);
					}
                }
            }

            foreach($commit->removed as $filename) {
                if (!$this->excludeFile($filename)) {
                    if (!$this->removeFile($filename)) {
                        GitHubAutoDeployment::log('error', 'Error while trying to remove file: ' . setCorrectFilePath($filename));
						$this->emailLog .= "\n".date('d.m.Y @ H:i:s').": !!! ERROR !!! removed: \t".$this->setCorrectFilePath($filename);
                        $errors = true;
                    } else {
						$this->emailLog .= "\n".date('d.m.Y @ H:i:s').": removed: \t".$this->setCorrectFilePath($filename);
					}
                }
            }
        }
        if (!$errors) {
            print('succes');
            GitHubAutoDeployment::log('deployment', 'All systems go!');
			$endTime = time();
			$timeSpent = round(($endTime-$this->startTime)*100)/100;
			$this->emailLog .= "\n\n".date('d.m.Y @ H:i:s',$endTime).": Deploy ended";
			$this->emailLog .= "\nDeployed in ".$timeSpent." seconds";
			$this->sendMail();
        } else {	
            GitHubAutoDeployment::log('deployment', 'Deployment failed');
			$this->emailLog .= "\n\n".date('d.m.Y @ H:i:s',$endTime).": !!! DEPLOY FAILED !!!";
			$this->sendMail();
	
		}
    }

    protected function addFile($file) {

            $url  = 'https://raw.github.com/' . $this->settings['username'] . '/' . $this->settings['repository'] . '/' . $this->branch . '/' . $file;
			$file = $this->setCorrectFilePath($file);
            $path = $this->settings['branches'][$this->branch] . '/' . $file;
            $this->createDir($path);

            $content = @file_get_contents($url);

            // upload
            return @file_put_contents($path, $content);
    }

    protected function removeFile($file) {
			
			$file = $this->setCorrectFilePath($file);
            $path = $this->settings['branches'][$this->branch] . '/' . $file;

            // delete
            return @unlink($path);
    }

	protected function setCorrectFilePath($file) {
		// check folder
		if ($this->settings['folder'] != '') {
			$file = substr($file,strlen($this->settings['folder'])+1);
		}
		return $file;
	}

    /**
     *  Check that current file is not in an exlcude list
     *      If returned true - omit.
     */
    protected function excludeFile($file){

        $file_info = pathinfo($file);

		// check folder
		if ($this->settings['folder'] != '') {
			if (strpos($file_info['dirname'], $this->settings['folder'], 0) !== 0) {
				return true;
			}
		}

        // check file
        foreach ($this->settings['excludeFiles'] as $ex_file) {
            
            // file type
            if (strpos($ex_file,'*.') === 0) {
                $extension = substr($ex_file,2);

                if ($extension == $file_info['extension']) {
                    return true;
                }
                        
            // file 
            } elseif (strpos($ex_file, '*') === 0) {
                $filename = substr($ex_file,1);

                if ($filename == $file_info['basename']) {
                    return true;
                }
            
            // file with path
            } elseif ($file == $ex_file) {
                return true;
            }
        }
        
        // check dir
        foreach ($this->settings['excludeDirs'] as $ex_dir) {
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
                            date('d.m.Y @ H:i:s') . ' - ' . strtoupper($status) . ' - ' . $message . "\n",
                            FILE_APPEND
                        );

        if ($die) {
            print($message);
			$this->emailLog .= "\n\n".$message;
			$this-semdMail();
            die;
        }
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

	/**
     *  Send email with log
     */
    protected function sendMail(){
        $to = $this->settings['email'];
		$subject = "Deploy";
		$body = $this->emailLog;
		mail($to, $subject, $body);
    }

}
?>