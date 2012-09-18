Zepto(function($){

	// Set container in middle
	$('#container').css('margin-top',Math.round(($(window).height()-$('#container').height())/2));
	setInterval(validate,100);
	
	function validate() {
		var validated = true;
		
		//username
		$('#username').removeClass('validated');
		if (md5($('input[name="username"]').val()) == $('input[name="username_check"]').val()) {
			$('#username').addClass('validated');
		} else {
			validated = false;
		}
		
		//password
		$('#password').removeClass('validated');
		if (md5($('input[name="password"]').val()) == $('input[name="password_check"]').val()) {
			$('#password').addClass('validated');
		} else {
			validated = false;
		}
		
		//branch
		$('#branch').removeClass('validated');
		if ($('input[name="branch"]').val() != '') {
			$('#branch').addClass('validated');
		} else {
			validated = false;
		}
		
		$('#submit-rebuild').removeClass('disabled');
		if (!validated) {
			$('#submit-rebuild').addClass('disabled');
		}
		
	}
	
});



