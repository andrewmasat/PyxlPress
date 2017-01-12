define([
	'jquery',
	'underscore',
	'backbone',
	'bootstrap',
	'views/global/pluginPlayView',
	'text!templates/pyxlate/templates/header.html',
	'text!templates/pyxlate/templates/footer.html',
	], function($, _, Backbone, Bootstrap, PluginPlay, Header, Footer){

	'use strict';

	// Views
	var HomeView = Backbone.View.extend({
		el: $('.stage'),
		initialize: function() {
			var that = this;

			// If zombie is present, kill it.
			if (this.$el.length > 0) {
				this.$el.empty();
				this.$el.unbind();
			}
		},
		render: function(pageData) {
			var that = this;
			that.package = [];
			that.package.page = pageData;

			that.buildPage();
		},
		buildPage: function() {
			var that = this;
			var fileName = that.package.page.pageFileName;
			if (!fileName) {
				fileName = that.package.page.pagePermalink;
			}
			var pageUrl = 'text!templates/pyxlate/templates/'+fileName+'.html';

			require([pageUrl], function (pageTemplate) {
				// Pages Template
				that.$el.html(_.template(pageTemplate, {data:that.package}));
				that.$el.prepend(_.template(Header, {data:that.package}));
				that.$el.append(_.template(Footer, {data:that.package}));

				var pluginPlay = new PluginPlay();
				pluginPlay.findPlugin();
			});
		}
	});

	return HomeView;
});