define([
	'jquery',
	'underscore',
	'backbone',
	'models/plugins/pluginPlay'
	], function($, _, Backbone, PluginPlay){

	'use strict';

	var PluginPlayView = Backbone.View.extend({
		el: $('body'),
		initialize: function() {
			

		},
		findPlugin: function() {
			var content = $('.content').text();

			console.log(content);
			if (content !== undefined && content.split('[[')[1]) {
				content = content.split('[[')[1].split(']]')[0];

				var action = window.location.pathname.split('/').pop();
				var pluginPlay = new PluginPlay();
				pluginPlay.urlRoot = window.location.pathname.replace(action, '') + 'pyxl-include/plugins/class.pluginPlay.php';
				pluginPlay.fetch({
					data: {request: action},
					processData: true,
					success: function(model, data) {
						console.log('working');
					}
				});
			}
		}
	});

	return PluginPlayView;
});