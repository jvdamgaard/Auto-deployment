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
                 'payload' => $_POST['payload'],
                 'username' => 'jvdamgaard',
                 'repo' => 'm.adaptivedesignstudio.com',
                 'branches' => array(
                         'master' => dirname(__FILE__),
                         'release-*' => dirname(dirname(__FILE__)).'/test-m.adaptivedesignstudio.com',
                         'hotfix_*' => dirname(dirname(__FILE__)).'/test-m.adaptivedesignstudio.com',
                         'develop' => dirname(dirname(__FILE__)).'/dev-m.adaptivedesignstudio.com'
                     )
             );

include_once('deploy/githubautodeployment.php');

// Auto deploy form GitHub
if(isset($_POST['payload']) && !empty($_POST['payload'])) {
    $deploy =   new GitHubAutoDeployment(
					array_merge(
						array('payload' => $_POST['payload']),
						$settings
					)
				);
				
// Rebuild site 				
} else (isset($_POST['rebuild']) && !empty($_POST['rebuild'])) {

// Show web interface	
} else {
	
}
 
?>