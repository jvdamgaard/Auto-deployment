Zepto(function($) {

	// Init
	centerContainer();
	$(window).on('resize', centerContainer);

	var branches = [];

	var validated = {
		username: false,
		password: false,
		branch = false
	};
	
	var validationCheck = {
		username: $('input[name="username_check"]').val(),
		password: $('input[name="password_check"]').val()	
	}

	$.ajaxJSONP({
		url: 'https://api.github.com/repos/'+$('input[name="github_username"]').val()+'/'+$('input[name="github_repository"]').val()+'/branches?callback=?',
		success: function(res){
			for (var i in res.data) {
				branches.push(res.data[i].name);
			}

			$('.wrapper').animate({opacity:1},500,'ease-in-out');
			
			
	
			var name = '';
			var val = '';
			
			$('input').on('keydown paste input', function(e){
				if (val != e.target.value || name != e.target.name) {
					val = e.target.value;
					name = e.target.name;
					validate(val, name);
				}
			});
		}
	});

	function validate(val, name) {

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

		validated[name] = inputValidated;
		
		// TO-DO: not working
		console.log(validated);
		
		var readyForBuild = true;
		for (i in validated) {
			if (!validated[i]) {
				readyForBuild = false;
			}	
		}

		if (readyForBuild) {
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
