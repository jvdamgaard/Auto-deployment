Zepto(function($) {

	// Init
	centerContainer();
	setTimeout(function(){
				$('.wrapper').animate({opacity:1},500,'ease-in-out')
			},
		200);
	$(window).bind('resize', centerContainer);

	var branches = [];

	$.ajaxJSONP({
		url: 'https://api.github.com/repos/'+$('input[name="github_username"]').val()+'/'+$('input[name="github_repository"]').val()+'/branches?callback=?',
		success: function(res){
			for (var i in res.data) {
				branches.push(res.data[i].name);
				console.log(res.data[i].name);
			}
			setInterval(validate, 100);
		}
	});

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
		if ($.inArray($('input[name="branch"]').val(),branches) !== -1) {
			$('#branch').addClass('validated');
		} else {
			validated = false;
		}

		$('#submit-rebuild').removeClass('disabled');
		if (!validated) {
			$('#submit-rebuild').addClass('disabled');
		}

	}

	// Set container in middle
	function centerContainer() {
		$('#container').animate({'margin-top': Math.round(($(window).height() - $('#container').height()) / 2)},100);
	}

});
