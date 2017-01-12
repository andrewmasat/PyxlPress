define([
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone){

		'use strict';

		var Activate = Backbone.Model.extend({
			urlRoot: window.location.pathname.split('/pyxl-core/')[0] + '/pyxl-include/account/class.activate.php'
		});

	return Activate;
});