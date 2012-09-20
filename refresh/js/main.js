Zepto(function($) {

	// Init
	centerContainer();
	$(window).on('resize', centerContainer);

	var branches = [];

	var validated = [];
	validated.username = 'false';
	validated.password = 'false';
	validated.branch = 'false';
	
	var validationCheck = [];
	validationCheck.username = $('input[name="username_check"]').val();
	validationCheck.password = $('input[name="password_check"]').val();
	
	var name = '';
	var val = '';

	$.ajaxJSONP({
		url: 'https://api.github.com/repos/'+$('input[name="github_username"]').val()+'/'+$('input[name="github_repository"]').val()+'/branches?callback=?',
		success: function(res){
			for (var i in res.data) {
				branches.push(res.data[i].name);
			}

			$('.wrapper').animate({opacity:1},500,'ease-in-out');
			
			$('input').on('keydown paste input', function(e){
				if (val != e.target.value || name != e.target.name) {
					val = e.target.value;
					name = e.target.name;
					validate();
				}
			});
		}
	});

	function validate() {

		var inputValidated = false;

		if (name == 'username' || name == 'password') {
			inputValidated = (md5(val) == validationCheck[name]);
		} else if (name == 'branch') {
			inputValidated = ($.inArray(val,branches) !== -1);
		}

		if (inputValidated) {
			$('#'+name).addClass('validated');
		} else {
			$('#'+name).removeClass('validated');
		}

		validated[name] = ''+inputValidated;
		
		// TO-DO: not working
		console.log(validated);

		if ($.inArray('false',validated) !== -1) {
			$('#submit-rebuild').removeClass('disabled');
		} else {
			$('#submit-rebuild').addClass('disabled');
		}

	}

	// Set container in middle
	function centerContainer() {
		var marginTop = Math.round(($(window).height() - $('#container').height()) / 2);
		if (marginTop < 0) {
			marginTop = 0;
		}
		$('#container').css({'margin-top': marginTop});
	}

});
