define([
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone){

		'use strict';

		var Login = Backbone.Model.extend({
			urlRoot: window.location.pathname.split('/pyxl-core/')[0] + '/pyxl-include/account/class.login.php'
		});

	return Login;
});