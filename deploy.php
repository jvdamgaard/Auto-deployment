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

include_once('githubautodeployment.php');

// debug
$payload = '{ "after": "0a78de2f755af3fb8ddd85b5347a63b1f8933f04", "before": "ed49b022087b45f43d43b3e560505d376cbb7fe0", "commits": [ { "added": [], "author": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "committer": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "distinct": true, "id": "8159fe4cf1fb827f8652c8b442559888a15519b1", "message": "sdf", "modified": [ "index.html" ], "removed": [], "timestamp": "2012-09-15T03:34:38-07:00", "url": "https://github.com/jvdamgaard/m.adaptivedesignstudio.com/commit/8159fe4cf1fb827f8652c8b442559888a15519b1" }, { "added": [], "author": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "committer": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "distinct": true, "id": "2227ca497f2e71d6a8d35ed5b2aa438be2200a3f", "message": "dgf", "modified": [ "index.html" ], "removed": [], "timestamp": "2012-09-15T03:41:57-07:00", "url": "https://github.com/jvdamgaard/m.adaptivedesignstudio.com/commit/2227ca497f2e71d6a8d35ed5b2aa438be2200a3f" }, { "added": [], "author": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "committer": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "distinct": true, "id": "0a78de2f755af3fb8ddd85b5347a63b1f8933f04", "message": "js compression test", "modified": [ "js/main.js" ], "removed": [], "timestamp": "2012-09-15T13:00:22-07:00", "url": "https://github.com/jvdamgaard/m.adaptivedesignstudio.com/commit/0a78de2f755af3fb8ddd85b5347a63b1f8933f04" } ], "compare": "https://github.com/jvdamgaard/m.adaptivedesignstudio.com/compare/ed49b022087b...0a78de2f755a", "created": false, "deleted": false, "forced": false, "head_commit": { "added": [], "author": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "committer": { "email": "jakob_damgaard@hotmail.com", "name": "Jakob Viskum Damgaard", "username": "jvdamgaard" }, "distinct": true, "id": "0a78de2f755af3fb8ddd85b5347a63b1f8933f04", "message": "js compression test", "modified": [ "js/main.js" ], "removed": [], "timestamp": "2012-09-15T13:00:22-07:00", "url": "https://github.com/jvdamgaard/m.adaptivedesignstudio.com/commit/0a78de2f755af3fb8ddd85b5347a63b1f8933f04" }, "pusher": { "name": "none" }, "ref": "refs/heads/master", "repository": { "created_at": "2012-09-10T13:10:41-07:00", "description": "Testsite for mobile inteaction", "fork": false, "forks": 0, "has_downloads": true, "has_issues": true, "has_wiki": true, "language": "JavaScript", "name": "m.adaptivedesignstudio.com", "open_issues": 0, "owner": { "email": "jakob_damgaard@hotmail.com", "name": "jvdamgaard" }, "private": false, "pushed_at": "2012-09-15T13:00:34-07:00", "size": 748, "stargazers": 0, "url": "https://github.com/jvdamgaard/m.adaptivedesignstudio.com", "watchers": 0 } }';

// Auto deploy form GitHub
//if(isset($_POST['payload']) && !empty($_POST['payload'])) {
    $deploy =   new GitHubAutoDeployment(
					array_merge(
						//array('payload' => $_POST['payload']),
                        // debig
                        array('payload' => $payload),
						$settings
					)
				);
				
// Rebuild site 				
//} else (isset($_POST['rebuild']) && !empty($_POST['rebuild'])) {

// Show web interface	
//} else {
	
//}
 
?>