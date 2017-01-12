define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/login/register',
	'views/global/globalView',
	'text!templates/login/registerTemplate.html',
	'text!templates/login/registerCompleteTemplate.html',
	'text!templates/login/registerFailTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Register, GlobalEvents, RegisterTemplate, RegisterCompleteTemplate, RegisterFailTemplate){

	'use strict';

	// Views
	var RegisterView = Backbone.View.extend({
		el: $('.stage'),
		events: {
		 	'submit #register': 'register',
		 	'click .tryagain': 'render'
		},
		initialize: function() {
			var that = this;

			// If zombie is present, kill it.
			if (this.$el.length > 0) {
				this.$el.empty();
				this.$el.unbind();
			}
		},
		render: function() {
			var that = this;
			this.global = new GlobalEvents();

			var enforcer = new Enforcer();
			enforcer.fetch({
				data: {location:that.global.location()},
				processData: true,
				success: function (model,data) {
					that.package = {'account':data};
					that.package.page = [];
					that.package.page.section = that.global.location();

					if (that.global.isInstalled(that)) {
						that.buildPage();
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
					
					if (that.package.environment.allowRegister === '0') {
						Backbone.history.navigate('../pyxl-core/', {trigger:true});
					} else {
						// Build Template
						that.$el.html(_.template(RegisterTemplate, {data:that.package}));
					}

					that.global.initView(that);
				}
			});
		},
		register: function(event) {
			var that = this, isValid;
			var registerDetails = $(event.currentTarget).serializeObject();
			var registerSubmit = new Register();
			var registerBtn = $('#submitEdit').button('loading');

			var registerInput = this.$el.find('#register .form-group');

			// Remove errors
			$('#register').find('.form-group').removeClass('has-error');

			$('input').each(function() {
				if ($(this).val() === '') {
					isValid = false;
					$(this).parent().parent().addClass('has-error');
				}
			});

			if (!$('#register .form-group').hasClass('has-error')) {
				// Check passwords
				if (registerDetails.password === registerDetails.confirmpassword && registerDetails.password.length >= 6) {
					isValid = true;
					$('#register .error').fadeOut();
				} else {
					isValid = false;
					$('#password').parent().parent().addClass('has-error');
					$('#confirmpassword').parent().parent().addClass('has-error');
					if (registerDetails.password.length < 6) {
						$('#register .error').text('Password must be 6 characters long.').fadeIn();
					} else {
						$('#register .error').text('Passwords do not match.').fadeIn();
					}
				}
			} else {
				isValid = false;
			}

			if (isValid) {
				registerSubmit.save(registerDetails, {
					error: function (xhr, message, thrownError) {
						that.loginError(message.responseText);
					},
					success: function (model,data) {
						if (data.result === 'SUCCESS') {
							that.package.account = {
								'username': data.username,
								'email': data.email
							};
							that.$el.html(_.template(RegisterCompleteTemplate, {data:that.package}));
						} else {
							that.$el.html(_.template(RegisterFailTemplate, {data:data}));
						}
					}
				});
			} else {
				setTimeout(function() {
					registerBtn.button('reset');
				}, 2000);
			}
			return false;
		}
	});

	return RegisterView;
});