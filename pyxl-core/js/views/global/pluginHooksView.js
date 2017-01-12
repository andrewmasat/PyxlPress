define([
	'jquery',
	'underscore',
	'backbone',
	'models/plugins/plugins',
	], function($, _, Backbone, Hooks){

	'use strict';

	$.fn.serializeObject = function() {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

	var PluginHooksView = Backbone.View.extend({
		el: $('body'),
		initialize: function() {
			

		},
		initView: function(state) {
			var that = this;
			this.state = state;
			this.account = state.package.account;
			this.plugins = state.package.environment.plugins;

			$.each(this.plugins, function(i, val) {
				if (val.hookType === 'admin_create_page') {
					that.adminCreatePage(val);
				} else if (val.hookType === 'admin_sidebar_menu') {
					that.adminSidebarMenu(val);
				} else if (val.hookType === 'create_view') {
					that.createView(val);
				}
			});
		},
		adminCreatePage: function(data) {
			var that = this;
			var pageUrl = '';
			if (data.pluginName) {
				if (this.state.package.page.section === data.pluginName) {
					if (this.state.package.page.option) {
						pageUrl = 'text!plugins/'+data.pluginName+'/templates/'+this.state.package.page.option+'.html';
					} else {
						pageUrl = 'text!plugins/'+data.pluginName+'/templates/'+data.pluginName+'.html';
					}

					require([pageUrl], function (pluginAdminTemplate) {
						// Plugin Admin Template
						$('.pageEntry').append(_.template(pluginAdminTemplate, {data:that.state.package}));
					});
				}
			}
		},
		adminSidebarMenu: function(data) {
			if (this.account.level === data.pluginSecLevel) {
				// Build Menu Icons
				if (data.menuIcon) {
					data.menuIcon = '<i class="fa '+data.menuIcon+'"></i>';
				}

				// Build Menu Data Target
				data.menuTarget = data.menuUrl.split('/');
				var menuTargetLast = data.menuTarget.pop();

				// Build Menu Button & Sub Button
				var menuBtn = '<li data-target="'+menuTargetLast+'" data-origin="plugin"><a href="'+this.state.package.environment.navUrl+'plugins/'+data.menuUrl+'" title="'+data.menuTitle+'" data-page="'+menuTargetLast+'">'+data.menuIcon+data.menuTitle+'</a></li>';
				if (data.menuLevel === 0) {
					$('section.sidebar .pages > ul').append(menuBtn);
				} else if (data.menuLevel === 1) {
					if (!$('section.sidebar .pages > ul > li[data-target='+data.menuTarget[0]+'] > ul').length) {
						$('section.sidebar .pages > ul > li[data-target='+data.menuTarget[0]+']').append('<ul></ul>');
					}
					$('section.sidebar .pages > ul > li[data-target='+data.menuTarget[0]+'] > ul').append(menuBtn);
				}
			}
		},
		createView: function(data) {
			var state = this.state;
			var viewUrl = window.location.pathname.split('/pyxl-core/')[0]+'/pyxl-content/plugins/'+data.pluginName+'/'+data.viewUrl;

			require([viewUrl], function (IndexView) {
				var pluginView = new IndexView();
				pluginView.render(state.package);
			});
		}
	});

	return PluginHooksView;
});