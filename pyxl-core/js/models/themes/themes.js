define([
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone){

		'use strict';

		var Themes = Backbone.Model.extend({
			urlRoot: window.location.pathname.split('/pyxl-core/')[0] + '/pyxl-include/themes/class.themes.php'
		});

	return Themes;
});