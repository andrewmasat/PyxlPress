define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'views/global/globalView',
	'text!templates/home/header.html',
	'text!templates/home/sidebar.html',
	'text!templates/home/footer.html'
	//'text!templates/login/template.html'
	], function($, _, Backbone, Environment, GlobalEvents, Header, Sidebar, Footer){

	'use strict';

	// Views
	var View = Backbone.View.extend({
		el: $('.stage'),
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

					if (that.package.account.username) {
						Backbone.history.navigate('../pyxl-core/', {trigger:true});
					} else {
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
					that.$el.html(_.template(Header, {data:that.package}));
					// that.$el.append(_.template(RegisterTemplate, {data:that.package}));
					that.$el.append(_.template(Footer, {data:that.package}));
				}
			});
		}
	});

	return View;
});