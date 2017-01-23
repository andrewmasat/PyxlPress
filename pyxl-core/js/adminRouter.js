// Filename: router.js
define([
	'jquery',
	'underscore',
	'backbone',
	'views/page/activateView',
	'views/admin/adminView',
	'views/home/homeView',
	'views/login/loginView',
	'views/page/pluginView',
	'views/page/profileView',
	'views/login/registerView',
	'views/page/themesView',
	'views/page/welcomeView'
	], function($, _, Backbone, ActivateView, AdminView, HomeView,
		LoginView, PluginView, ProfileView, RegisterView, ThemesView, WelcomeView) {

		'use strict';

		var AppRouter = Backbone.Router.extend({
			routes: {
				// Activate Page
				'activate': 'activate',
				'activate/:activateId': 'activate',

				// Admin Page
				'admin': 'admin',
				'admin/:page': 'admin',
				'admin/:page/:id': 'admin',

				// Login/Forgot Page
				'login': 'login',
				'login/:page': 'login',
				'login/:page/:forgotId': 'login',

				// Plugin Page
				'plugins': 'plugins',
				'plugins/:page': 'plugins',
				'plugins/:page/:option': 'plugins',
				'plugins/:page/:option/:id': 'plugins',

				// Profile Page
				'profile': 'profile',
				'profile/:page': 'profile',

				// Register Page
				'register': 'register',

				// Themes Page
				'themes': 'themes',
				'themes/:page': 'themes',
				'themes/:page/:file': 'themes',

				// Register Page
				'welcome': 'welcome',

				// Default
				'*actions': 'defaultAction'
			}
		});

		var initialize = function(){

			var app_router = new AppRouter();

			app_router.on('route:activate', function (activateId) {
				var activateView = new ActivateView();
				activateView.render({activateId:activateId});
			});

			app_router.on('route:admin', function (page, id) {
				var adminView = new AdminView();
				adminView.render({location:page, id:id});
			});

			app_router.on('route:login', function (page, forgotId) {
				var loginView = new LoginView();
				loginView.render({location:page, forgotId: forgotId});
			});

			app_router.on('route:plugins', function (page, option, id) {
				var pluginView = new PluginView();
				pluginView.render({location:page, option: option, id: id});
			});

			app_router.on('route:profile', function (page) {
				var profileView = new ProfileView();
				profileView.render({location:page});
			});

			app_router.on('route:register', function () {
				var registerView = new RegisterView();
				registerView.render();
			});

			app_router.on('route:themes', function (page, file) {
				var themesView = new ThemesView();
				themesView.render({location:page, file:file});
			});

			app_router.on('route:welcome', function (page) {
				var welcomeView = new WelcomeView();
				welcomeView.render({location:page});
			});

			app_router.on('route:defaultAction', function (actions) {
				var homeView = new HomeView();
				homeView.render();
			});
			
			Backbone.history.start({
				pushState: true,
				hashChange: false,
				root: getRoot()
			});
		};

		var getRoot = function() {
			var url = window.location.pathname.split('/pyxl-core/');
			return url[0]+'/pyxl-core/';
		};
	return { 
		initialize: initialize
	};
});