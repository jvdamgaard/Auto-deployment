MBP.scaleFix();
MBP.hideUrlBarOnLoad();
MBP.preventZoom();

Zepto(function($) {

	var	branches = [],
		validated = {
			username: false,
			password: false,
			branch: false },
		validationCheck = {
			username: $('input[name="username_check"]').val(),
			password: $('input[name="password_check"]').val() },
		readyForBuild = false;

	// Init
	centerContainer();
	$(window).on('resize', centerContainer);
	$('#submit-rebuild').on('click', function(e) {
		e.preventDefault();
		if (readyForBuild) {
			$('#submit-rebuild').addClass('active');
			$('#formbox').animate({opacity:0.2},500,'ease-in-out');
			deploy( $('#branch input').val() );
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
			$('input[name="username"], input[name="password"], input[name="branch"]').on('keydown paste input', function(e){
				
				$('#message').hide();
				
				if (val != e.target.value || name != e.target.name) {
					val = e.target.value;
					name = e.target.name;
					validate(val, name);
				}
			});
		}
	});

	function validate(val, name) {

		if (name == 'username' || name == 'password') { 	
			validated[name] = (md5(val) == validationCheck[name]);
		} else if (name == 'branch') {
			validated[name] = ($.inArray(val,branches) !== -1);
		}

		if (validated[name]) {
			$('#'+name).addClass('validated');
		} else {
			$('#'+name).removeClass('validated');
		}
		
		// If all input is validated
		readyForBuild = (validated.username && validated.password && validated.branch);

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

	function deploy(deployBranch) {

		$.ajaxJSONP({
			url: 'https://api.github.com/repos/'+$('input[name="github_username"]').val()+'/'+$('input[name="github_repository"]').val()+'/branches?callback=?',
			success: function(res){
				for (var i in res.data) {
					if (res.data[i].name == deployBranch) {
						getTree(res.data[i].commit.sha, deployBranch);
						break;
					}
				}
			},
			error: function(xhr, type){
				errorOnDeploy(xhr, type);
			}
		});
	}

	function getTree(sha, branch) {
		$.ajaxJSONP({
			url: 'https://api.github.com/repos/'+$('input[name="github_username"]').val()+'/'+$('input[name="github_repository"]').val()+'/git/trees/'+sha+'?recursive=1&callback=?',
			success: function(res){
				createPayload(res.data.tree, branch);
			},
			error: function(xhr, type){
				errorOnDeploy(xhr, type);
			}
		});
	}

	function createPayload(files, branch) {
		var payload = '{';
		
		payload += '"ref": "refs/heads/'+branch+'", ';
		
		var added = [];
		for (var i in files) {
			if (files[i].type == 'blob') {
				added.push(files[i].path);
			}
		}
		payload += '"commits": [{"added": ["'+added.join('","')+'"],"modified": [],"removed": []}]';
		
		payload += '}';
		callAutoDeploy(payload, branch);
	}

	function callAutoDeploy(deployPayload, branch) {
		$.ajax({
			type: 'POST',
			data: { payload: deployPayload },
			success: function(data){

				// Succes
				if (data == 'succes') {
					$('#submit-rebuild').removeClass('active');
					$('#formbox').animate({opacity:1},500,'ease-in-out');
					$('#message').html(branch+' deployed').show().addClass('validated');
					$('#username input, #password input, #branch input').val('');
					validate('', 'username');
					validate('', 'password');
					validate('', 'branch');

				// Error	
				} else {
					$('#formbox').animate({opacity:1},500,'ease-in-out');
					$('#message').html(data).show().removeClass('validated');
				}
			},
			error: function(xhr, type){
				errorOnDeploy(xhr, type);
			}
		});
	}

	function errorOnDeploy(xhr, type) {
		$('#message').val(type).show().removeClass('validated');
	}

});
