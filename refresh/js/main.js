Zepto(function($) {

	var branches = [],
	validated = {
		username: false,
		password: false,
		branch: false
	},
	validationCheck = {
		username: $('input[name="username_check"]').val(),
		password: $('input[name="password_check"]').val()	
	},
	readyForBuild = true;
	inputFieldIds = ['#username','#password','#branch'];

	// Init
	centerContainer();
	$(window).on('resize', centerContainer);
	$('#submit-rebuild').on('click', function(e) {
		e.preventDefault();
		if (readyForBuild) {
			$('#formbox').animate({opacity:0.2},500,'ease-in-out');
			//$('#rebuildLoader').animate({opacity:1,zIndex:0.2},500,'ease-in-out');
		}
	});

	// Use github api for branches
	$.ajaxJSONP({
		url: 'https://api.github.com/repos/'+$('input[name="github_username"]').val()+'/'+$('input[name="github_repository"]').val()+'/branches?callback=?',
		success: function(res){
			for (var i in res.data) {
				branches.push(res.data[i].name);
			}

			// Show for when branches has been ajax'ed
			$('#formbox').animate({opacity:1},500,'ease-in-out');
			$('.wrapper').animate({opacity:1},500,'ease-in-out');

			var name = '',
			val = '';
			
			// Validate when input change
			$(inputFieldIds.join(',')).on('keydown paste input', function(e){
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
		
		// If all input is validated
		var allValidated = true;
		for (var i in validated) {
			if (!validated[i]) {
				allValidated = false;
			}	
		}
		readyForBuild = allValidated;

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
		$('#formbox').css({'padding-top': marginTop});
	}

});
