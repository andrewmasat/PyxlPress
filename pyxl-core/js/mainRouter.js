// Filename: router.js
define([
	'jquery',
	'underscore',
	'backbone',
	'models/pages/pages',
	'models/plugins/pluginPlay'
	], function($, _, Backbone, Pages, PluginPlay) {

		'use strict';

		var AppRouter = Backbone.Router.extend({
			routes: {
				// Default
				'*page': 'defaultAction',
				'*page/:id': 'defaultAction'
			}
		});

		var initialize = function(){

			// Get Url
			var url = $('.stage').data('url');

			// Router Constructor
			var app_router = new AppRouter();

			app_router.on('route:defaultAction', function (page, id) {
				// Pages Constructor
			});
			
			Backbone.history.on("route", function() {
				pageConstructor(url);
			});
			Backbone.history.on("route", _trackPageview);

			var action = window.location.href.replace(url,'').split('/');
			var getRoot = new Pages();
			getRoot.urlRoot = url + '/pyxl-include/admin/class.pages.php';
			getRoot.fetch({
				data: {request: action[1]},
				processData: true,
				success: function(model, data) {
					var url = window.location.pathname.split(data.pagePermalink);

					Backbone.history.start({
						pushState: true,
						hashChange: false,
						root: url[0]
					});
				}
			});
		};

		var _trackPageview = function() {
			var url = Backbone.history.getFragment();
			ga('send', 'pageview', "/"+url);
		};

		var urlConstructor = function () {


		};

		var pageConstructor = function(url) {
			var action = Backbone.history.getFragment().split('/');
			var getPages = new Pages();
			getPages.urlRoot = url + '/pyxl-include/admin/class.pages.php';
			getPages.fetch({
				data: {request: action[0]},
				processData: true,
				success: function(model, data) {
					// Is Installed
					if (data.isInstalled === 'NO_CONFIG') {
						window.location = '/pyxl-core/install';
					} else {
						require(['../../pyxl-content/themes/'+data.theme+'/views/indexView'], function (IndexView) {
							var pageView = new IndexView();
							pageView.render(data);
							
							if (data.pluginHooks.length !== 0) {
								var plugins = [];
								var getPlugin = new PluginPlay();
								getPlugin.urlRoot = url + '/pyxl-include/plugins/class.pluginPlay.php';

								pageView.render(data);
								$.each(data.pluginHooks, function(i, val) {
									plugins[i] = val;

									getPlugin.fetch({
										data: {request: val},
										processData: true,
										success: function(model, hook) {
											if (hook.options) {
												var folder = hook.options[0];
												require(['../../pyxl-content/plugins/'+folder+'/views/indexView'], function (pluginIndex) {
													var pluginView = new pluginIndex();

													pluginView.renderHook(hook.options);
												});
											}
										}
									});
								});
								
							}
						});
					}

				}
			});
		};
	return { 
		initialize: initialize
	};
});