define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/media/media',
	'views/global/globalView',
	'text!templates/home/page.html',
	'text!templates/general/header.html',
	'text!templates/general/footer.html',
	'text!templates/general/sidebar.html',
	'text!templates/media/mediaTemplate.html',
	'text!templates/media/mediaListTemplate.html',
	'text!templates/media/mediaUploadTemplate.html',
	'text!templates/media/mediaPrepUploadTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Media, GlobalEvents, 
				Page, Header, Footer, Sidebar, MediaTemplate, MediaListTemplate, MediaUploadTemplate, MediaPrepUploadTemplate){

	'use strict';

	// Views
	var MediaView = Backbone.View.extend({
		el: $('.stage'),
		events: {
			'click .mediaDrop': 'openFileDrop',
			'change input[type=file]': 'prepareUpload'
		},
		initialize: function() {
			var that = this;

			// If zombie is present, kill it.
			if (this.$el.length > 0) {
				this.$el.empty();
				this.$el.unbind();
			}
		},
		render: function(page) {
			var that = this;
			this.global = new GlobalEvents();

			var enforcer = new Enforcer();
			enforcer.fetch({
				data: {location:that.global.location()},
				processData: true,
				success: function (model,data) {
					that.package = {'account':data};
					that.package.page = page;
					that.package.page.section = that.global.location();

					if (that.global.isInstalled(that)) {
						if (that.package.account.userId) {
							that.buildPage();
						} else {
							Backbone.history.navigate('../pyxl-core/', {trigger:true});
						}
					}
				}
			});
		},
		buildPage: function() {
			var that = this;
			var environment = new Environment();

			environment.fetch({
				success: function(data) {
					that.package.environment = data.toJSON();
					
					// Build Template
					that.$el.html(_.template(Page, {data:that.package}));
					$('.header').append(_.template(Header, {data:that.package}));
					$('.sidebar').append(_.template(Sidebar, {data:that.package}));
					$('.footer').append(_.template(Footer, {data:that.package}));

					if (that.package.page.location === 'upload') {
						that.uploadMediaPage();
					} else {
						that.mediaPage();
					}
				}
			});
		},
		mediaPage: function() {
			this.package.page.title = 'Media';

			var that = this;
			var getMedia = new Media();
			getMedia.fetch({
				data: {request: "getMedia"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(MediaTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(MediaListTemplate, {data:data.mediaList}));

					that.global.initView(that);
					that.global.activePage('media');
				}
			});
		},
		uploadMediaPage: function() {
			this.package.page.title = 'Upload';

			var that = this;
			$('.content').append(_.template(MediaTemplate, {data:that.package}));
			$('.pageEntry').append(_.template(MediaUploadTemplate, {data:that.package}));

			that.global.initView(that);
			that.global.activePage('upload');
		},
		openFileDrop: function() {
			$('input[type=file]').trigger('click');
		},
		prepareUpload: function(e) {
			this.package.page.files = e.target.files;

			console.log(this.package.page.files);

			$('.prepUploadList').prepend(_.template(MediaPrepUploadTemplate, {data:this.package.page.files}));
		},
	});

	return MediaView;
});