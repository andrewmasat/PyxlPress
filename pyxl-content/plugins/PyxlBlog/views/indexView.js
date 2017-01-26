define([
	'jquery',
	'underscore',
	'backbone',
	'bootstrap',
	'models/plugins/plugins',
	'text!plugins/PyxlBlog/templates/postList.html',
	'text!plugins/PyxlBlog/templates/editPost.html',
	'text!plugins/PyxlBlog/templates/postInfo.html',
	'text!plugins/PyxlBlog/templates/blog.html'
	], function($, _, Backbone, Bootstrap, Hooks, PostListPage, PostEditFields, PostInfo, BlogHook){

	'use strict';

	// Views
	var PyxlBlog = Backbone.View.extend({
		el: $('.stage'),
		events: {
			'click #submitPost': 'submitPostNew',
			'click #submitPostEdit': 'submitPostEdit'
		},
		render: function(state) {
			this.package = state;

			if (!this.package.page.option) {
				this.postList();
			} else if (this.package.page.option === 'edit') {
				this.postEditPage();
			}
		},
		renderHook: function(options) {
			if (options[1] === 'blog') {
				this.blog(options);
			}
		},
		postList: function() {
			var hookData = {
				hookType: 'pb_post_list',
				hookData: '',
				pluginName: 'PyxlBlog'
			};

			var hooks = new Hooks({request: "triggerHook"});
			hooks.save(hookData, {
				success: function(model, data) {
					$('.postList').html(_.template(PostListPage, {data:data.postList}));
				}
			});
		},
		submitPostNew: function(e) {
			e.preventDefault();
			
			var postData = {
				content: $('#content').val(),
				title: $('#title').val(),
				siteUrl: this.package.environment.siteUrl
			};
			var hookData = {
				hookType: 'pb_post_save',
				hookData: postData,
				pluginName: 'PyxlBlog'
			};

			var hooks = new Hooks({request: "triggerHook"});
			hooks.save(hookData, {
				success: function(model, data) {
					Backbone.history.navigate('plugins/PyxlBlog/edit/'+data.id, {trigger:true});
				}
			});
		},
		postEditPage: function() {
			var hookData = {
				hookType: 'pb_post_edit_get',
				hookData: this.package.page.id,
				pluginName: 'PyxlBlog'
			};

			var hooks = new Hooks({request: "triggerHook"});
			hooks.save(hookData, {
				success: function(model, data) {
					$('.editPost').html(_.template(PostEditFields, {data:data}));
					$('.postInfo').html(_.template(PostInfo, {data:data}));
				}
			});
		},
		submitPostEdit: function(e) {
			e.preventDefault();
			
			var postData = {
				id: this.package.page.id,
				content: $('#content').val(),
				title: $('#title').val(),
				siteUrl: this.package.environment.siteUrl
			};
			var hookData = {
				hookType: 'pb_post_edit_save',
				hookData: postData,
				pluginName: 'PyxlBlog'
			};

			var hooks = new Hooks({request: "triggerHook"});
			hooks.save(hookData, {
				success: function(model, data) {
					console.log(data);
				}
			});
		},
		blog: function() {
			$('div[data-plugin="blog"]').text('hello');
		}
	});

	return PyxlBlog;
});