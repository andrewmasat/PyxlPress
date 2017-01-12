define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/profile/profile',
	'models/login/activate',
	'views/global/globalView',
	'text!templates/home/page.html',
	'text!templates/general/header.html',
	'text!templates/general/footer.html',
	'text!templates/general/sidebar.html',
	'text!templates/login/activateTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Profile, Activate, GlobalEvents, 
				Page, Header, Footer, Sidebar, ActivateTemplate){

	'use strict';

	// Views
	var ActivateView = Backbone.View.extend({
		el: $('.stage'),
		initialize: function() {
			var that = this;

			// If zombie is present, kill it.
			if (this.$el.length > 0) {
				this.$el.empty();
				this.$el.unbind();
			}
		},
		render: function(token) {
			var that = this;
			this.global = new GlobalEvents();

			var enforcer = new Enforcer();
			enforcer.fetch({
				data: {location:that.global.location()},
				processData: true,
				success: function (model,data) {
					that.package = {'account':data};
					that.package.token = token;
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
					
					// Build Template
					that.$el.html(_.template(Page, {data:that.package}));
					$('.header').append(_.template(Header, {data:that.package}));
					$('.sidebar').append(_.template(Sidebar, {data:that.package}));
					$('.footer').append(_.template(Footer, {data:that.package}));

					that.activationPage();
				}
			});
		},
		activationPage: function() {
			var that = this;

			var activate = new Activate();
			if (this.package.token.activateId) {
				var tokenKey = this.package.token.activateId.split('|');
				activate.fetch({
					data: {tokenId:tokenKey[0],tokenEmail:tokenKey[1]},
					processData: true,
					success: function(model, data) {
						$('.content').html(_.template(ActivateTemplate, {data:data}));

						that.global.initView(that);
					}
				});
			} else {
				activate.fetch({
					data: {tokenId:'',tokenEmail:''},
					processData: true,
					success: function(model, data) {
						$('.content').html(_.template(ActivateTemplate, {data:data}));

						that.global.initView(that);
					}
				});
			}
		}
	});

	return ActivateView;
});