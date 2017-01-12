define([
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone){

		'use strict';

		var Update = Backbone.Model.extend({
			urlRoot: window.location.pathname.split('/pyxl-core/')[0] + '/pyxl-include/admin/class.update.php'
		});

	return Update;
});