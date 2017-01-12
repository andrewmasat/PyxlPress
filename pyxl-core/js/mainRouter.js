// Filename: router.js
define([
	'jquery',
	'underscore',
	'backbone',
	'models/pages/pages'
	], function($, _, Backbone, Pages) {

		'use strict';

		var AppRouter = Backbone.Router.extend({
			routes: {
				// Default
				'*page': 'defaultAction'
			}
		});

		var initialize = function(){
			// Router Constructor
			var app_router = new AppRouter();

			app_router.on('route:defaultAction', function (page) {
				// Pages Constructor
				
			});
			
			Backbone.history.on("route", function() {
				pageConstructor();
			});
			Backbone.history.on("route", _trackPageview);

			var action = window.location.pathname.split('/').pop();
			var getRoot = new Pages();
			getRoot.urlRoot = window.location.pathname.replace(action, '') + 'pyxl-include/admin/class.pages.php';
			getRoot.fetch({
				data: {request: action},
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

		var pageConstructor = function() {

			var action = Backbone.history.getFragment();
			var getPages = new Pages();
			getPages.urlRoot = window.location.pathname.replace(action, '') + 'pyxl-include/admin/class.pages.php';
			getPages.fetch({
				data: {request: action},
				processData: true,
				success: function(model, data) {
					// Is Installed
					if (data.isInstalled === 'NO_CONFIG') {
						window.location = '/pyxl-core/install';
					} else {
						require(['../../pyxl-content/themes/'+data.theme+'/views/indexView'], function (IndexView) {
							var pageView = new IndexView();
							pageView.render(data);
						});
					}

				}
			});
		};
	return { 
		initialize: initialize
	};
});