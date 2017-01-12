define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/login/forgot',
	'models/login/login',
	'models/login/resetPassword',
	'views/global/globalView',
	'text!templates/login/loginTemplate.html',
	'text!templates/login/forgotTemplate.html',
	'text!templates/login/resetPasswordTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Forgot, Login, ResetPassword, GlobalEvents,
							LoginTemplate, ForgotTemplate, ResetPasswordTemplate){

	'use strict';

	// Views
	var LoginView = Backbone.View.extend({
		el: $('.stage'),
		events: {
		 	'submit #passwordLogin': 'login',
		 	'submit #forgotPassword': 'forgot',
		 	'submit #resetPassword': 'resetPassword'
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
							Backbone.history.navigate('../pyxl-core/', {trigger:true});
						} else {
							that.buildPage();
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

					if (that.package.page.location === 'forgot') {
						that.forgotPage();
					} else if (that.package.page.location === 'password' && that.package.page.forgotId) {
						that.resetPasswordPage();
					} else {
						that.loginPage();
					}
					
					that.global.initView(that);
				}
			});
		},
		loginPage: function() {
			// Build Template
			this.$el.html(_.template(LoginTemplate, {data:this.package}));
			$('#username').focus();
		},
		forgotPage: function() {
			// Build Template
			this.$el.html(_.template(ForgotTemplate, {data:this.package}));
			$('#username').focus();
		},
		resetPasswordPage: function() {
			// Build Template
			this.$el.html(_.template(ResetPasswordTemplate, {data:this.package}));
			$('#password').focus();
		},
		login: function(event) {
			var that = this;
			var loginDetails = $(event.currentTarget).serializeObject();
			var loginSubmit = new Login();

			var loginInput = this.$el.find('#passwordLogin .form-group');

			loginSubmit.save(loginDetails, {
				success: function (model,data) {
					// Change login view
					if (data.result) {
						that.loginError(data.result);
					} else if (data.disabled == 1) {
						that.loginError(data.disabled);
					} else{
						$('.errorMessage').text('');
						if (loginInput.hasClass('has-error')) {
							loginInput.removeClass('has-error').addClass('has-success');
						}

						if (!data.time) {
							Backbone.history.navigate('welcome', {trigger:true});
						} else {
							Backbone.history.navigate('../pyxl-core/', {trigger:true});
						}
					}
				}
			});
			return false;
		},
		loginError: function(fail) {
			var error = this.$el.find('#passwordLogin .form-group, #forgotPassword .form-group, #resetPassword .form-group');

			if (fail === 'BAD_LOGIN') {
				error.addClass('has-error animated shake');
				$('.errorMessage').text('Your Username or Password are incorrect');
			} else if (fail === 'EMPTY_FIELD') {
				error.addClass('has-error animated shake');
				$('.errorMessage').text('A field is empty');
			} else if (fail === 'MISSING_USERNAME') {
				error.addClass('has-error animated shake');
				$('.errorMessage').text('Missing a Username or Email Address');
			} else if (fail === 'BAD_PASSWORD') {
				error.addClass('has-error animated shake');
				$('.errorMessage').text('Passwords do not match');
			} else if (fail === 'BAD_FORGETID' || fail === 'NO_USER' ) {
				error.addClass('has-error animated shake');
				$('.errorMessage').text('Please resend your forgot email again');
			} else if (fail === '1') {
				error.addClass('has-error animated shake');
				$('.errorMessage').text('Your account has been disabled');
			} else {
				error.addClass('has-error animated shake');
				$('.errorMessage').text('You may have attempted to login to many times. Please try again later.');
			}
		},
		forgot: function(event) {
			var that = this;
			var forgotDetails = $(event.currentTarget).serializeObject();
			var forgotSubmit = new Forgot();

			var forgotInput = this.$el.find('#forgotPassword .form-group');

			forgotSubmit.save(forgotDetails, {
				success: function (model,data) {
					$('.successMessage').addClass('hidden');

					// Change login view
					if (data.result) {
						that.loginError(data.result);
					} else if (data.disabled == 1) {
						that.loginError(data.disabled);
					} else{
						$('.errorMessage').text('');

						$('.successMessage.hidden span').text(data.email);
						$('.successMessage.hidden').removeClass('hidden');
						forgotInput.removeClass('has-error').addClass('has-success');
					}
				}
			});
			return false;
		},
		resetPassword: function(event) {
			var that = this;
			var resetDetails = $(event.currentTarget).serializeObject();
			var resetSubmit = new ResetPassword();

			var resetInput = this.$el.find('#resetPassword .form-group');

			if (this.package.page.forgotId) {
				var forgotCode = this.package.page.forgotId.split('|');
				resetDetails.forgotId = forgotCode[0];
				resetDetails.email = forgotCode[1];

				resetSubmit.save(resetDetails, {
					success: function (model,data) {
						// Change login view
						if (data.result) {
							that.loginError(data.result);
						} else if (data.disabled == 1) {
							that.loginError(data.disabled);
						} else{
							$('.errorMessage').text('');
							
							Backbone.history.navigate('login', {trigger:true});
						}
					}
				});
			}

			return false;
		}
	});

	return LoginView;
});