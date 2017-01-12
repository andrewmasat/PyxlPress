define([
	'jquery',
	'underscore',
	'backbone',
	'bootstrap',
	'pace',
	'views/global/pluginHooksView',
	'models/login/logout',
	'models/general/notifications',
	'text!templates/general/notifications.html'
	], function($, _, Backbone, Bootstrap, Pace, PluginHooks, Logout, Notifications, NotificationTemplate){

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

	var GlobalView = Backbone.View.extend({
		el: $('body'),
		initialize: function() {
			this.$el.unbind();

			this.statusAlertsFetch();
		},
		events: {
			'click .logout': 'logout',
			'click .statusBtn': 'statusAlertsToggle',
			'click .statusRefresh': 'statusAlertsRefresh'
		},
		initView: function(state) {
			// Check Debug
			if (state.package.environment.debug === '1') {
				console.log(state);
			}
			
			// Plugin Activate
			this.plugins = new PluginHooks();
			this.plugins.initView(state);

			// Check Status Alerts
			this.statusAlertsFetch(false);
			
			// Update Sidebar location
			this.activeSidebarLink();
		},
		isInstalled: function(state) {
			if (state.package.account.isInstalled === 'NO_CONFIG') {
				window.location = '/pyxl-core/install';
			} else {
				return true;
			}
		},
		location: function() {
			var location = window.location.pathname.split('/pyxl-core/').pop();
			if (location === '') {
				location = 'home';
			} else if(location.split('/')[0] === 'plugins' && location.split('/')[1] !== undefined) {
				location = location.split('/')[1];
			} else {
				location = location.split('/')[0];
			}
			return location;
		},
		logout: function() {
			var logout = new Logout();
			logout.fetch({
				success: function() {
					Backbone.history.navigate('logout', {trigger:true});
				}
			});
		},
		activePage: function(activePage) {
			$('a[data-page="' + activePage + '"]').parent().addClass('active');
		},
		activeSidebarLink: function() {
			var location = this.location();
			$('section.sidebar .pages > ul > li').each(function() {
				if ($(this).data('target') === location) {
					$(this).addClass('active');
				}
			});
		},
		notice: function(title, message, state, priority, hide) {
			var notice = $('.notice');
			// State: On/Off
			if (state === 'on') {
				notice.addClass('on');
			} else {
				notice.removeClass('on');
			}
			// Priority
			if (priority === 'success') {
				notice.addClass('success').removeClass('danger warning');
			} else if (priority === 'danger') {
				notice.addClass('danger').removeClass('success warning');
			} else if (priority === 'warning') {
				notice.addClass('warning').removeClass('success danger');
			} else {
				notice.removeClass('danger success warning');
			}
			// Message
			notice.html('<span>'+title+':</span> '+message);
			// Hide after timeout
			if (hide) {
				setTimeout(function() {
					notice.removeClass('on');
				}, 10000);
			}
		},
		statusAlertsFetch: function(active) {
			var getNotice = new Notifications();
			getNotice.fetch({
				data: {request: "getNotice"},
				processData: true,
				success: function(model, data) {
					data.active = active;
					$('.statusAlerts').html(_.template(NotificationTemplate, {data:data}));
				}
			});
		},
		statusAlertsToggle: function() {
			$('.statusBtn, .statusPanel').toggleClass('active');

			if ($('.statusPanel').hasClass('active')) {
				var clearAlerts = new Notifications();
				clearAlerts.fetch({
					data: {request: "clearAlerts"},
					processData: true,
					success: function() {
						
					}
				});
			}
		},
		statusAlertsRefresh: function() {
			this.statusAlertsFetch(true);
		}
	});

	return GlobalView;
});