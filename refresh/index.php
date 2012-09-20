<!DOCTYPE html>
<!--[if IEMobile 7 ]>    <html class="no-js iem7"> <![endif]-->
<!--[if (gt IEMobile 7)|!(IEMobile)]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title></title>
        <meta name="description" content="">
        <meta name="HandheldFriendly" content="True">
        <meta name="MobileOptimized" content="320">
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="cleartype" content="on">

        <link rel="stylesheet" href="refresh/css/normalize.css">
        <link rel="stylesheet" href="refresh/css/main.css">
        <link href="http://fonts.googleapis.com/css?family=Open Sans&subset=latin" rel="stylesheet" type="text/css">
        <script src="refresh/js/vendor/modernizr-2.6.1.min.js"></script>
    </head>
    <body>
		<div class="wrapper">
        	<div id="container">
            	<h1>Rebuild site</h1>
            	<form>
                	<p id="username"><input type="text" name="username" placeholder="Username" autofocus="autofocus"></p>
                	<p id="password"><input type="password" name="password" placeholder="Password"></p>
                	<p id="branch"><input type="text" name="branch" placeholder="Branch"></p>
                	<p><a href="#" class="btn disabled" id="submit-rebuild">Rebuild</a></p>
                	<input type="hidden" name="rebuild" value="true">
					<input type="hidden" name="username_check" value="<?php echo $user['username'];?>">
					<input type="hidden" name="password_check" value="<?php echo $user['password'];?>">
					<input type="hidden" name="github_username" value="<?php echo $settings['username'];?>">
					<input type="hidden" name="github_repository" value="<?php echo $settings['repository'];?>">
            	</form>
        	</div>
		</div>

        <script src="refresh/js/vendor/zepto.min.js"></script>
        <script src="refresh/js/main.js"></script>
    </body>
</html>
