<?php
/**	
 *  Github automatic deployment script for php projects
 *
 *  This file will make your project available on a live server several seconds
 *      after each push was made to a repository by any user
 *
 *  TO-DO
 *		- for private repositories
 *		- auto-update
 */

$version = '0.8.0';

$settings =	array(
				'username' => 'jvdamgaard',
				'repository' => 'm.adaptivedesignstudio.com',
				'email' => 'jakob_damgaard@hotmail.com',
				'branches' => array(
					'master' => dirname(dirname(__FILE__)),
					'release-*' => dirname(dirname(dirname(__FILE__))).'/test-m.adaptivedesignstudio.com',
					'hotfix-*' => dirname(dirname(dirname(__FILE__))).'/test-m.adaptivedesignstudio.com',
					'develop' => dirname(dirname(dirname(__FILE__))).'/dev-m.adaptivedesignstudio.com'
					),
				'ip' => '86.52.85.72',	
				'folder' => 'publish' // only deploy files from this subfolder. E.g. used with ant-build-script. Don't include front and end slashes
			);

// used to login for refresh build
// use http://md5-hash-online.waraxe.us/ for md5
$user = array(
            'username' =>   '198d8824ba56a3ad72b0afdc2f04c7c2',
            'password' =>   'f0d27884e73cfab47dc8ae408dfdcd2d'
        );

include_once('githubautodeployment.php');
include_once('checkforupdate.php');

$versionCheck = new VersionCheck($version);

if (!$versionCheck->isUpToDate()) {
	echo 'Should be updated';
}

// Auto deploy form GitHub
if(isset($_POST['payload']) && !empty($_POST['payload'])) {
    $deploy =   new GitHubAutoDeployment(
					array_merge(
						array('payload' => $_POST['payload']),
						$settings
					)
				);

// Show web interface	
} else {
	include_once('refresh/index.php');
}
 
?>