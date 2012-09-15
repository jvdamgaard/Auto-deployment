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
                         'hotfix_*' => dirname(dirname(dirname(__FILE__))).'/test-m.adaptivedesignstudio.com',
                         'develop' => dirname(dirname(dirname(__FILE__))).'/dev-m.adaptivedesignstudio.com'
                     )
             );

include_once('githubautodeployment.php');

// debug
//$payload = '{\"pusher\":{\"name\":\"jvdamgaard\",\"email\":\"jakob_damgaard@hotmail.com\"},\"repository\":{\"name\":\"m.adaptivedesignstudio.com\",\"created_at\":\"2012-09-10T13:10:41-07:00\",\"size\":696,\"has_wiki\":true,\"private\":false,\"watchers\":0,\"language\":\"JavaScript\",\"url\":\"https://github.com/jvdamgaard/m.adaptivedesignstudio.com\",\"fork\":false,\"pushed_at\":\"2012-09-15T03:42:08-07:00\",\"has_downloads\":true,\"open_issues\":0,\"has_issues\":true,\"description\":\"Testsite for mobile inteaction\",\"stargazers\":0,\"forks\":0,\"owner\":{\"name\":\"jvdamgaard\",\"email\":\"jakob_damgaard@hotmail.com\"}},\"forced\":false,\"after\":\"2227ca497f2e71d6a8d35ed5b2aa438be2200a3f\",\"head_commit\":{\"modified\":[\"index.html\"],\"added\":[],\"removed\":[],\"timestamp\":\"2012-09-15T03:41:57-07:00\",\"author\":{\"name\":\"Jakob Viskum Damgaard\",\"username\":\"jvdamgaard\",\"email\":\"jakob_damgaard@hotmail.com\"},\"url\":\"https://github.com/jvdamgaard/m.adaptivedesignstudio.com/commit/2227ca497f2e71d6a8d35ed5b2aa438be2200a3f\",\"id\":\"2227ca497f2e71d6a8d35ed5b2aa438be2200a3f\",\"distinct\":true,\"message\":\"dgf\",\"committer\":{\"name\":\"Jakob Viskum Damgaard\",\"username\":\"jvdamgaard\",\"email\":\"jakob_damgaard@hotmail.com\"}},\"deleted\":false,\"ref\":\"refs/heads/master\",\"commits\":[{\"modified\":[\"index.html\"],\"added\":[],\"removed\":[],\"timestamp\":\"2012-09-15T03:41:57-07:00\",\"author\":{\"name\":\"Jakob Viskum Damgaard\",\"username\":\"jvdamgaard\",\"email\":\"jakob_damgaard@hotmail.com\"},\"url\":\"https://github.com/jvdamgaard/m.adaptivedesignstudio.com/commit/2227ca497f2e71d6a8d35ed5b2aa438be2200a3f\",\"id\":\"2227ca497f2e71d6a8d35ed5b2aa438be2200a3f\",\"distinct\":true,\"message\":\"dgf\",\"committer\":{\"name\":\"Jakob Viskum Damgaard\",\"username\":\"jvdamgaard\",\"email\":\"jakob_damgaard@hotmail.com\"}}],\"before\":\"8159fe4cf1fb827f8652c8b442559888a15519b1\",\"compare\":\"https://github.com/jvdamgaard/m.adaptivedesignstudio.com/compare/8159fe4cf1fb...2227ca497f2e\",\"created\":false}';

// Auto deploy form GitHub
if(isset($_POST['payload']) && !empty($_POST['payload'])) {
    $deploy =   new GitHubAutoDeployment(
					array_merge(
						array('payload' => $_POST['payload']),
                        // debig
                        //array('payload' => $payload),
						$settings
					)
				);
				
// Rebuild site 				
} else (isset($_POST['rebuild']) && !empty($_POST['rebuild'])) {

// Show web interface	
} else {
	
}
 
?>