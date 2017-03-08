define([
	'jquery',
	'underscore',
	'backbone',
	'bootstrap',
	'models/plugins/plugins',
	'views/global/pluginNoticeView',
	'text!plugins/PyxlBlog/templates/postList.html',
	'text!plugins/PyxlBlog/templates/editPost.html',
	'text!plugins/PyxlBlog/templates/editPostOptions.html',
	'text!plugins/PyxlBlog/templates/postInfo.html',
	'text!plugins/PyxlBlog/templates/blog.html'
	], function($, _, Backbone, Bootstrap, Hooks, Notice, PostListPage, PostEditFields, PostEditOptions, PostInfo, BlogHook){

	'use strict';

	// Views
	var PyxlBlog = Backbone.View.extend({
		el: $('.stage'),
		events: {
			'click #submitPost': 'submitPostNew',
			'click #submitPostEdit': 'submitPostEdit',
			'click #deletePostEdit': 'deletePostEdit'
		},
		render: function(state) {
			this.package = state;
			this.notice = new Notice();

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
			var that = this;
			
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
					that.notice.alert('Success','Your post has been saved.','on','success',true);
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
					$('.editPostOptions').html(_.template(PostEditOptions, {data:data}));
				}
			});
		},
		submitPostEdit: function(e) {
			e.preventDefault();
			var that = this;
			
			var postData = {
				pb_id: this.package.page.id,
				pb_content: $('#content').val(),
				pb_status: $('#status').val(),
				pb_title: $('#title').val(),
				pb_siteUrl: this.package.environment.siteUrl
			};
			var hookData = {
				hookType: 'pb_post_edit_save',
				hookData: postData,
				pluginName: 'PyxlBlog'
			};

			var submitBtn = $('#submitPostEdit').button('loading');

			var hooks = new Hooks({request: "triggerHook"});
			hooks.save(hookData, {
				success: function(model, data) {
					that.notice.alert('Success','Your post has been updated.','on','success',true);
					submitBtn.button('reset');

					$('.editPost').html(_.template(PostEditFields, {data:data.hookData}));
					$('.postInfo').html(_.template(PostInfo, {data:data.hookData}));
					$('.editPostOptions').html(_.template(PostEditOptions, {data:data.hookData}));
				}
			});
		},
		deletePostEdit: function() {
			var hookData = {
				hookType: 'pb_post_delete',
				hookData: this.package.page.id,
				pluginName: 'PyxlBlog'
			};

			var submitBtn = $('#deletePostEdit').button('loading');

			var hooks = new Hooks({request: "triggerHook"});
			hooks.save(hookData, {
				success: function(model, data) {
					submitBtn.button('reset');
					Backbone.history.navigate('plugins/PyxlBlog', {trigger:true});
				}
			});
		},
		blog: function() {
			var hookData = {
				hookType: 'pb_post_list',
				hookData: '',
				pluginName: 'PyxlBlog'
			};

			var hooks = new Hooks({request: "triggerHook"});
			var action = Backbone.history.getFragment();
			hooks.urlRoot = window.location.pathname.replace(action, '') + 'pyxl-include/plugins/class.plugins.php';
			hooks.save(hookData, {
				success: function(model, data) {
					$('div[data-plugin="blog"]').html(_.template(BlogHook, {data:data.postList}));
				}
			});
		}
	});

	return PyxlBlog;
});