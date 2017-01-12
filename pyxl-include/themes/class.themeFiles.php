<?php

/*
 *
 * File Name: class.themeFiles.php
 * Description: Repository of Theme default files
 *
 */

// Index View JavaScript (Default)
function indexView($themeName) {
	$view = "define([
	'jquery',
	'underscore',
	'backbone',
	'bootstrap',
	'text!templates/".$themeName."/templates/header.html',
	'text!templates/".$themeName."/templates/footer.html',
	], function($, _, Backbone, Bootstrap, Header, Footer){

	'use strict';

	// Views
	var HomeView = Backbone.View.extend({
		el: $('.stage'),
		initialize: function() {
			var that = this;

			// If zombie is present, kill it.
			if (this.\$el.length > 0) {
				this.\$el.empty();
				this.\$el.unbind();
			}
		},
		render: function(pageData) {
			var that = this;
			that.package = [];
			that.package.page = pageData;

			that.buildPage();
		},
		buildPage: function() {
			var that = this;
			var fileName = that.package.page.pageFileName;
			if (!fileName) {
				fileName = that.package.page.pagePermalink;
			}
			var pageUrl = 'text!templates/".$themeName."/templates/'+fileName+'.html';

			require([pageUrl], function (pageTemplate) {
				// Pages Template
				that.\$el.html(_.template(pageTemplate, {data:that.package}));
				that.\$el.prepend(_.template(Header, {data:that.package}));
				that.\$el.append(_.template(Footer, {data:that.package}));
			});
		}
	});

	return HomeView;
});";

	return $view;
}

// StyleSheet (Default)
function styleView($themeName, $themeAuthor, $themeDesc, $themeTags) {
	$view = "/*
 *
 * Theme: ".$themeName."
 * Author: ".$themeAuthor."
 * Description: ".$themeDesc."
 * Tags: ".$themeTags."
 *  
 */";

	return $view;
}