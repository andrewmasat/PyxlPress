define([
	'jquery',
	'underscore',
	'backbone',
	'bootstrap'
	], function($, _, Backbone, Bootstrap){

	'use strict';

	var PluginNoticeView = Backbone.View.extend({
		el: $('body'),
		initialize: function() {
			this.$el.unbind();
		},
		alert: function(title, message, state, priority, hide) {
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
		}
	});

	return PluginNoticeView;
});