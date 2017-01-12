define([
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone){

		'use strict';

		var Plugins = Backbone.Model.extend({
			urlRoot: window.location.pathname.split('/pyxl-core/')[0] + '/pyxl-include/plugins/class.plugins.php'
		});

	return Plugins;
});