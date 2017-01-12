$(document).ready(function() {
	$.fn.serializeObject = function() {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

	var siteUrl = document.location.origin;
	var sitePath = document.location.pathname.split('/');
	var siteAddress = '';
	$.each(sitePath, function(i,val) {
		if (val !== '' && val !== 'pyxl-core' && val !== 'install') {
			siteAddress = siteAddress + '/' + val;
		}
	});

	$('#siteUrl').val(siteUrl + siteAddress);

	$('.returnHome').click(function() {
		window.location.href = siteUrl + siteAddress + '/pyxl-core/welcome';
	});

	$('#install').submit(function(data) {
		data.preventDefault();
		var form = $(data.currentTarget).serializeObject();
		form.request = 'install';
		
		$('#submitInstall').button('loading');
		$('.step1 input').parent().removeClass('has-error has-success');
		$('.step1 .alert').addClass('hidden');

		$.ajax({
			url: 'class.install.php',
			type: 'POST',
			data: form,
			dataType: 'json',
			success: function(data) {
				if (data.connect === 'failed') {
					$('.step1 input').parent().addClass('has-error');
					$('#submitInstall').button('reset');
					$('.step1 .alert').html('<strong>Install Failed</strong> - Please check your database settings.').addClass('alert-danger').removeClass('alert-success hidden');
				} else {
					$('.step1 input').parent().addClass('has-success');
					$('#submitInstall').text('Install Complete');
					$('#submitInstall').button('reset');
					$('.step1 .alert').html('<strong>Install Successful</strong>').addClass('alert-success').removeClass('alert-danger hidden');

					setTimeout(function() {
						$('.step1').addClass('hidden');
						$('.step2').removeClass('hidden');
					}, 1000);
				}
			}
		});
	});

	$('#settings').submit(function(data) {
		data.preventDefault();
		var form = $(data.currentTarget).serializeObject();
		var isValid = true;
		form.request = 'settings';

		$('#submitSettings').button('loading');
		$('.step2 input').parent().removeClass('has-error has-success');
		$('.step2 .alert').addClass('hidden');

		$('.step2 input').each(function() {
			if ($(this).val() === '') {
				isValid = false;
				$(this).parent().addClass('has-error');
				$('.step2 .alert').html('<strong>Missing Settings</strong> - Please check all fields.').addClass('alert-danger').removeClass('alert-success hidden');
			}
		});

		if (isValid) {
			$.ajax({
				url: 'class.install.php',
				type: 'POST',
				data: form,
				dataType: 'json',
				success: function(data) {
					$('.step2 input').parent().addClass('has-success');
					$('#submitSettings').text('Settings Saved');
					$('#submitSettings').button('reset');
					$('.step2 .alert').html('<strong>Settings Saved</strong>').addClass('alert-success').removeClass('alert-danger hidden');

					setTimeout(function() {
						$('.step2').addClass('hidden');
						$('.step3').removeClass('hidden');
					}, 1000);
				}
			});
		}
	});

	$('#account').submit(function(data) {
		data.preventDefault();
		var form = $(data.currentTarget).serializeObject();
		var isValid = true;
		form.request = 'account';

		$('#submitAccount').button('loading');
		$('.step3 input').parent().removeClass('has-error has-success');
		$('.step3 .alert').addClass('hidden');

		$('.step3 input').each(function() {
			if ($(this).val() === '') {
				isValid = false;
				$(this).parent().addClass('has-error');
				$('.step3 .alert').html('<strong>Missing Account Info</strong> - Please check all fields.').addClass('alert-danger').removeClass('alert-success hidden');
			}
		});

		if (isValid) {
			$.ajax({
				url: 'class.install.php',
				type: 'POST',
				data: form,
				dataType: 'json',
				success: function(data) {
					$('.step3 input').parent().addClass('has-success');
					$('#submitAccount').text('Account Created');
					$('#submitAccount').button('reset');
					$('.step3 .alert').html('<strong>Account Created</strong>').addClass('alert-success').removeClass('alert-danger hidden');

					setTimeout(function() {
						$('.step3').addClass('hidden');
						$('.finish').removeClass('hidden');
					}, 1000);
				}
			});
		}
	});
});