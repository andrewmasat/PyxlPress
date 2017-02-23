define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/profile/profile',
	'models/profile/avatar',
	'views/global/globalView',
	'text!templates/home/page.html',
	'text!templates/general/header.html',
	'text!templates/general/footer.html',
	'text!templates/general/sidebar.html',
	'text!templates/profile/profileTemplate.html',
	'text!templates/profile/profileViewTemplate.html',
	'text!templates/profile/profileEditTemplate.html',
	'text!templates/profile/profileSettingsTemplate.html',
	'text!templates/profile/profilePasswordTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Profile, Avatar, GlobalEvents, 
				Page, Header, Footer, Sidebar, ProfileTemplate, ProfileViewTemplate,
				ProfileEditTemplate, ProfileSettingsTemplate, ProfilePasswordTemplate){

	'use strict';

	// Views
	var ProfileView = Backbone.View.extend({
		el: $('.stage'),
		events: {
			'submit #editProfile': 'submitProfilePage',
			'submit #submitSettingsPage': 'submitSettingsPage',
			'submit #submitPasswordPage': 'submitPasswordPage',
			'submit #saveAvatar': 'saveAvatar',
			'keyup #email': 'handleEmailUpdate',
			'click .resendActivation': 'resendActivation',
			'change input[type=file]': 'prepareUpload'
		},
		initialize: function() {
			var that = this;

			// If zombie is present, kill it.
			if (this.$el.length > 0) {
				this.$el.empty();
				this.$el.unbind();
			}
		},
		render: function(page) {
			var that = this;
			this.global = new GlobalEvents();

			var enforcer = new Enforcer();
			enforcer.fetch({
				data: {location:that.global.location()},
				processData: true,
				success: function (model,data) {
					that.package = {'account':data};
					that.package.page = page;
					that.package.page.section = that.global.location();

					if (that.global.isInstalled(that)) {
						if (that.package.account.userId) {
							that.buildPage();
						} else {
							Backbone.history.navigate('../pyxl-core/', {trigger:true});
						}
					}
				}
			});
		},
		buildPage: function() {
			var that = this;
			var environment = new Environment();

			environment.fetch({
				success: function(data) {
					that.package.environment = data.toJSON();
					
					// Build Template
					that.$el.html(_.template(Page, {data:that.package}));
					$('.header').append(_.template(Header, {data:that.package}));
					$('.sidebar').append(_.template(Sidebar, {data:that.package}));
					$('.footer').append(_.template(Footer, {data:that.package}));

					if (that.package.page.location === 'edit') {
						that.editProfilePage();
					} else if (that.package.page.location === 'settings') {
						that.editSettingsPage();
					} else if (that.package.page.location === 'password') {
						that.editPasswordPage();
					} else {
						that.myProfilePage();
					}
				}
			});
		},
		myProfilePage: function() {
			this.package.page.title = 'My Profile';

			var that = this;
			var getProfile = new Profile();
			getProfile.fetch({
				data: {request: "getProfile"},
				processData: true,
				success: function(model, data) {
					data.siteUrl = that.package.environment.siteUrl;

					$('.content').append(_.template(ProfileTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(ProfileViewTemplate, {data:data}));
					
					that.global.initView(that);
					that.global.activePage('profile');
				}
			});
		},
		editProfilePage: function() {
			this.package.page.title = 'Edit Profile';
			
			var that = this;
			var getEditProfile = new Profile();
			getEditProfile.fetch({
				data: {request: "getProfile"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(ProfileTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(ProfileEditTemplate, {data:data}));

					that.global.initView(that);
					that.global.activePage('edit');
				}
			});
		},
		handleEmailUpdate: function() {
			$('.newEmail').removeClass('hidden');
		},
		submitProfilePage: function(data) {
			var that = this;
			var profileData = $(data.currentTarget).serializeObject();
			var profileSubmit = new Profile({request: "saveProfile"});
			var profileBtn = $('#submitEdit').button('loading');

			profileSubmit.save(profileData, {
				success: function(model, data) {
					profileBtn.button('reset');
					that.global.notice('Success','Your profile has been saved.','on','success',true);
					$('#editProfile').find('.form-group').addClass('has-success');
					setTimeout(function() {
						Backbone.history.navigate('profile/edit', {trigger:true});
					}, 2000);
				}
			});

			return false;
		},
		prepareUpload: function(e) {
			this.package.page.files = e.target.files;

			if (e.target.files[0]) {
				if (e.target.files[0].size <= 100000) {
					var fileName = e.target.value.split( '\\' ).pop();
					if(fileName) {
						$('.fileLabel').text(fileName).removeClass('btn-danger btn-default').addClass('btn-success');
						$('#submitAvatar').prop('disabled', false);
					} else {
						$('.fileLabel').text('Choose an image').removeClass('btn-success btn-danger').addClass('btn-default');
						$('#submitAvatar').prop('disabled', true);
					}
				} else {
					$('.fileLabel').text('Your file size is to big').removeClass('btn-success btn-default').addClass('btn-danger');
				}
			} else {
				$('#submitAvatar').prop('disabled', true);
			}
		},
		saveAvatar: function(e) {
			e.stopPropagation();
			e.preventDefault();

			var that = this;
			var data = new FormData();
			$.each(this.package.page.files, function(key, value) {
				data.append(key, value);
			});

			var getSettings = new Avatar();
			getSettings.fetch({
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false, 
				contentType: false,
				success: function() {
					Backbone.history.navigate('profile/edit', {trigger:true});
				}
			});
		},
		editSettingsPage: function() {
			this.package.page.title = 'Edit Settings';
			
			var that = this;
			var getSettings = new Profile();
			getSettings.fetch({
				data: {request: "getProfile"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(ProfileTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(ProfileSettingsTemplate, {data:data}));

					that.global.initView(that);
					that.global.activePage('settings');
				}
			});
		},
		submitSettingsPage: function(data) {
			var that = this;
			var settingData = $(data.currentTarget).serializeObject();
			var settingSubmit = new Profile({request: "saveSettings"});
			var settingBtn = $('#submitEdit').button('loading');

			$(':checkbox').each(function() {
				if(!$(this).is(':checked')) {
					settingData.sendEmail = '0';
				} else {
					settingData.sendEmail = '1';
				}
			});

			settingSubmit.save(settingData, {
				success: function(model, data) {
					settingBtn.button('reset');
					that.global.notice('Success','Your settings has been saved.','on','success',true);
					$('#editProfile').find('.form-group').addClass('has-success');
					setTimeout(function() {
						$('#editProfile').find('.form-group').removeClass('has-success');
					}, 2000);
				}
			});

			return false;
		},
		editPasswordPage: function() {
			this.package.page.title = 'Edit Password';
			
			var that = this;
			var getProfile = new Profile();
			getProfile.fetch({
				data: {request: "getProfile"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(ProfileTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(ProfilePasswordTemplate, {data:data}));

					that.global.initView(that);
					that.global.activePage('password');
				}
			});
		},
		submitPasswordPage: function(data) {
			var that = this, isValid;
			var passwordData = $(data.currentTarget).serializeObject();
			var passwordSubmit = new Profile({request: "savePassword"});
			var passwordBtn = $('#submitEdit').button('loading');
			
			// Remove errors
			$('#submitPasswordPage').find('.form-group').removeClass('has-error');

			$('input').each(function() {
				if ($(this).val() === '') {
					isValid = false;
					$(this).parent().addClass('has-error');
				}
			});
			
			if (!$('#submitPasswordPage .form-group').hasClass('has-error')) {
				// Check passwords
				if (passwordData.newPassword === passwordData.confirmPassword && passwordData.currentPassword !== passwordData.newPassword && passwordData.newPassword.length >= 8) {
					isValid = true;
				} else {
					isValid = false;
					$('#password').parent().addClass('has-error');
					$('#password2').parent().addClass('has-error');
					if (passwordData.newPassword.length < 8) {
						that.global.notice('Important','Password must be 8 characters long.','on','waning',true);
					} else if (passwordData.currentPassword === passwordData.newPassword) {
						that.global.notice('Important','Cannot use the same password as current one.','on','waning',true);
					} else {
						that.global.notice('Important','Passwords do not match.','on','waning',true);
					}
				}
			} else {
				isValid = false;
			}

			if (isValid) {
				passwordSubmit.save(passwordData, {
					error: function (xhr, message, thrownError) {
						passwordBtn.button('reset');
						passwordBtn.text('Something is wrong').addClass('btn-danger').removeClass('btn-primary');
						if (message.responseText === 'BAD_LOGIN') {
							that.global.notice('Important','Cannot use the same password as current one.','on','waning',true);
						} else {
							that.global.notice('Error','Something went wrong','on','error',true);
						}
						setTimeout(function() {
							passwordBtn.text('Save').addClass('btn-primary').removeClass('btn-danger');
						}, 2000);
					},
					success: function() {
						// Clear passwords
						$('#submitPasswordPage').find('input').val('');
						$('#submitPasswordPage').find('.form-group').addClass('has-success');

						// Reset
						passwordBtn.button('reset');
						$('#currentPassword').focus();
						that.global.notice('Success','Your new password has been saved.','on','success',true);
						setTimeout(function() {
							$('#submitPasswordPage').find('.form-group').removeClass('has-success');
						}, 2000);
					}
				});
			} else {
				setTimeout(function() {
					passwordBtn.button('reset');
				}, 1000);
			}
			return false;
		},
		resendActivation: function(e) {
			var that = this;
			$(e.currentTarget).button('loading');
			var resendActivation = new Profile();
			resendActivation.fetch({
				data: {request: "resendActivation"},
				processData: true,
				success: function(model, data) {
					$(e.currentTarget)
						.attr('disabled', true)
						.text('Sent Activation Email')
						.removeClass('btn-danger')
						.addClass('btn-success');
					$('.has-feedback').removeClass('has-error').addClass('has-waning');
				}
			});
		}
	});

	return ProfileView;
});