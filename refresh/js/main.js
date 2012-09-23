MBP.scaleFix();
MBP.hideUrlBarOnLoad();
MBP.preventZoom();

Zepto(function($) {

	var	validated = {
			username: false,
			password: false },
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
			deploy( $('#branch select').val() );
			// Build site
		}
	});

	// Use github api for branches
	$.ajaxJSONP({
		url: 'https://api.github.com/repos/'+$('input[name="github_username"]').val()+'/'+$('input[name="github_repository"]').val()+'/branches?callback=?',
		success: function(res){
			for (var i in res.data) {
				// branches.push(res.data[i].name);
				$('#branch select').append('<option value="'+res.data[i].name+'">'+res.data[i].name+'</option>');
			}

			// Show for when branches has been ajax'ed
			$('#formbox').animate({opacity:1},500,'ease-in-out');
			$('.wrapper').animate({opacity:1},500,'ease-in-out');

			var name = '',
				val = '';
			
			// Validate when input change
			$('input[name="username"], input[name="password"]').on('keydown paste input', function(e){
				if (val != e.target.value || name != e.target.name) {
					val = e.target.value;
					name = e.target.name;
					validate(val, name);
				}
			});
		}
	});

	function validate(val, name) {

		validated[name] = (md5(val) == validationCheck[name]);

		if (validated[name]) {
			$('#'+name).addClass('validated');
		} else {
			$('#'+name).removeClass('validated');
		}
		
		// If all input is validated
		readyForBuild = (validated.username && validated.password);

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
				errorOnDeploy();
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
				errorOnDeploy();
			}
		});
	}

	function createPayload(files, branch) {
		var payload = '{';
		
		payload += 'ref: refs/heads/'+branch+', ';
		
		var added = [];
		for (var i in files) {
			if (files[i].type == 'blob') {
				added.push(files[i].path);
			}
		}
		payload += 'commits: [{added: ["'+added.join('","')+'"],modified: []}]';
		
		payload += '}';
		console.log(payload);
		callAutoDeploy(payload);
	}

	function callAutoDeploy(deployPayload) {
		$.ajax({
			type: 'POST',
			data: { payload: deployPayload },
			success: function(data){
				// Succes
				console.log('Succes');
			},
			error: function(xhr, type){
				errorOnDeploy();
			}
		});
	}

	function errorOnDeploy() {
		console.log('Error');
	}

});
