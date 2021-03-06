<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <title>Deploy site</title>

        <link rel="stylesheet/less" type="text/css" href="refresh/less/main.less">
		<script src="refresh/js/vendor/less-1.3.0.min.js" type="text/javascript"></script>

        <link href="http://fonts.googleapis.com/css?family=Open Sans&subset=latin" rel="stylesheet" type="text/css">

        <script src="refresh/js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
		<div class="wrapper">
			<div id="formbox">
       			<div id="container">
           			<form>
						<p id="message">Deploy failed</p>
               			<p id="username"><input type="text" name="username" id="username_input" placeholder="Username" autofocus="autofocus" autocapitalize="off" autocorrect="off"></p>
               			<p id="password"><input type="password" name="password" id="password_input" placeholder="Password" autocapitalize="off" autocorrect="off"></p>
               			<p id="branch"><input type="text" name="branch" id="branch_input" placeholder="Branch" autocapitalize="off" autocorrect="off"></p>
               			<p><a href="#" class="btn disabled" id="submit-rebuild">Deploy</a></p>
        						<input type="hidden" name="username_check" value="<?php echo $user['username'];?>">
        						<input type="hidden" name="password_check" value="<?php echo $user['password'];?>">
        						<input type="hidden" name="github_username" value="<?php echo $settings['username'];?>">
        						<input type="hidden" name="github_repository" value="<?php echo $settings['repository'];?>">
                    <input type="hidden" name="payload" value="">
            		</form>
					<div id="rebuildLoader"></div>
				</div>
      </div>
		</div>
		<script>
			Modernizr.load([
					"refresh/js/vendor/zepto.min.js",
					"refresh/js/plugins.js",
					"refresh/js/helper.js",
					"refresh/js/main.js"
				]);
		</script
    </body>
</html>
