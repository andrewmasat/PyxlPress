define([
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone){

		'use strict';

		var Pages = Backbone.Model.extend({
			urlRoot: ''
		});

	return Pages;
});