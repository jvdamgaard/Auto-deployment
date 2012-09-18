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
 *      x options for class
 *      x class in seperate file
 */

$settings =	array(
                 'username' => 'jvdamgaard',
                 'repository' => 'm.adaptivedesignstudio.com',
                 'branches' => array(
                         'master' => dirname(dirname(__FILE__)),
                         'release-*' => dirname(dirname(dirname(__FILE__))).'/test-m.adaptivedesignstudio.com',
                         'hotfix-*' => dirname(dirname(dirname(__FILE__))).'/test-m.adaptivedesignstudio.com',
                         'develop' => dirname(dirname(dirname(__FILE__))).'/dev-m.adaptivedesignstudio.com'
                     )
             );

// used to login for refresh build
// use http://md5-hash-online.waraxe.us/ for md5
$user = array(
            'username' =>   '198d8824ba56a3ad72b0afdc2f04c7c2',
            'password' =>   'f0d27884e73cfab47dc8ae408dfdcd2d'
        );

include_once('githubautodeployment.php');

// Auto deploy form GitHub
if(isset($_POST['payload']) && !empty($_POST['payload'])) {
    $deploy =   new GitHubAutoDeployment(
					array_merge(
						array('payload' => $_POST['payload']),
						$settings
					)
				);
				
// Rebuild site 				
} elseif (  ( $_POST['rebuild'] == 'true') &&
            ( md5($_POST['usernmae']) == $user['username'] ) &&
            ( md5($_POST['password']) == $user['password'] ) ) {

// Show web interface	
} else {
	include_once('refresh/index.php');
}
 
?>