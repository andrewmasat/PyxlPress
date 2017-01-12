define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/plugins/plugins',
	'views/global/globalView',
	'text!templates/home/page.html',
	'text!templates/general/header.html',
	'text!templates/general/footer.html',
	'text!templates/general/sidebar.html',
	'text!templates/plugins/pluginsTemplate.html',
	'text!templates/plugins/pluginsListTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Plugins, GlobalEvents, 
				Page, Header, Footer, Sidebar, PluginsTemplate, PluginsListTemplate){

	'use strict';

	// Views
	var PluginView = Backbone.View.extend({
		el: $('.stage'),
		events: {
			'click .activatePlugin': 'activatePlugin',
			'click .deactivatePlugin': 'deactivatePlugin',
			'click .uninstallPlugin': 'uninstallPlugin'
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

					that.pluginPage();
				}
			});
		},
		pluginPage: function() {
			var that = this;
			var requestType = 'getPluginList';
			var getPlugins = new Plugins();

			this.package.page.title = 'Plugins';
			if (this.package.page.location === null) {
				this.package.page.location = "plugins";
			} else {
				requestType = 'getPlugin';
			}

			getPlugins.fetch({
				data: {request: requestType, pluginName: this.package.page.location, pluginPageId: this.package.page.id},
				processData: true,
				success: function(model, data) {
					if (that.package.page.location === 'plugins') {
						$('.content').append(_.template(PluginsTemplate, {data:that.package}));
						$('.pageEntry').append(_.template(PluginsListTemplate, {data:data}));
						that.global.activePage('plugins');
					} else {
						that.package.page.title = data.pluginLocation;
						$('.content').append(_.template(PluginsTemplate, {data:that.package}));
						$('.pageEntry').addClass(data.pluginLocation);
						that.global.activePage(data.pluginLocation);
					}

					that.global.initView(that);
				}
			});
		},
		activatePlugin: function(data) {
			var that = this;
			var pluginData = {
				pluginName: $(data.currentTarget).data('plugin'),
				pluginSecLevel: $(data.currentTarget).data('level'),
				pluginUrl: $(data.currentTarget).data('url'),
				pluginVersion: $(data.currentTarget).data('version')
			};
			var activatePlugin = new Plugins({request: "activatePlugin"});

			activatePlugin.save(pluginData, {
				success: function(model, data) {
					$('button.pluginBtn').prop('disabled', false).addClass('activatePlugin'); 

					$('.plugin').each(function(i,e) {
						if ($(e).data('plugin') === data.pluginName) {
							$(e).find('button.pluginBtn').prop('disabled', true).removeClass('activatePlugin').text('Initializing...'); 
						}
					});

					that.global.notice('Success', data.pluginName + ' has been activated!','on','success',true);
					location.reload();
				}
			});

			return false;
		},
		deactivatePlugin: function(data) {
			var that = this;
			var pluginData = {
				pluginName: $(data.currentTarget).data('plugin'),
				pluginUrl: $(data.currentTarget).data('url'),
				pluginVersion: $(data.currentTarget).data('version')
			};
			var deactivatePlugin = new Plugins({request: "deactivatePlugin"});

			deactivatePlugin.save(pluginData, {
				success: function(model, data) {
					$('button.pluginBtn').prop('disabled', false).addClass('deactivatePlugin'); 

					$('.plugin').each(function(i,e) {
						if ($(e).data('plugin') === data.pluginName) {
							$(e).find('button.pluginBtn').prop('disabled', true).removeClass('deactivatePlugin').text('Cleaning Up...'); 
						}
					});

					that.global.notice('Success', data.pluginName + ' has been deactivated.','on','success',true);
					location.reload();
				}
			});

			return false;
		},
		uninstallPlugin: function(data) {
			var that = this;
			var pluginData = {
				pluginName: $(data.currentTarget).data('plugin'),
				pluginUrl: $(data.currentTarget).data('url'),
				pluginVersion: $(data.currentTarget).data('version')
			};
			var uninstallPlugin = new Plugins({request: "uninstallPlugin"});

			uninstallPlugin.save(pluginData, {
				success: function(model, data) {
					that.global.notice('Success', data.pluginName + ' has been uninstalled.','on','success',true);
					location.reload();
				}
			});

			return false;
		}
	});

	return PluginView;
});