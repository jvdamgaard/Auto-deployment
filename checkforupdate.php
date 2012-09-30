<?php

class VersionCheck {
	// get latest version
	private $url  = 'https://raw.github.com/jvdamgaard/Auto-deployment/master/index.php';
	private $newVersion = '';
	private $oldVersion = '';
	
	function __construct($version){
		$content = @file_get_contents($url);
		$startPos = strpos($content, "\$version = '")+12;
		$endPos = strpost($content, "';",$startPos);
		$this->newVersion = substr($content,$startPos,$endPos-$startPos);
		$this->oldVersion = $version;
	}
	
	public function isUpToDate() {
		return ($this->oldVersion == $this->newVersion);
	}

?>