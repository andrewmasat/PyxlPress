define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/themes/themes',
	'views/global/globalView',
	'text!templates/home/page.html',
	'text!templates/general/header.html',
	'text!templates/general/footer.html',
	'text!templates/general/sidebar.html',
	'text!templates/themes/themesTemplate.html',
	'text!templates/themes/themesModalsTemplate.html',
	'text!templates/themes/themesListTemplate.html',
	'text!templates/themes/themesEditTemplate.html',
	'text!templates/themes/themesEditPageTemplate.html',
	'text!templates/themes/themesCreatePageTemplate.html',
	'text!templates/themes/themesCreateThemeTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Themes, GlobalEvents, 
				Page, Header, Footer, Sidebar, ThemesTemplate, ThemesModalsTemplate,
				ThemesListTemplate, ThemesEditTemplate, ThemesEditPageTemplate,
				ThemesCreatePageTemplate, ThemesCreateThemeTemplate){

	'use strict';

	// Views
	var ThemesView = Backbone.View.extend({
		el: $('.stage'),
		events: {
			'click .activateTheme': 'activateTheme',
			'click .previewTheme': 'previewTheme',
			'click #deleteFile': 'handleDeleteFile',
			'click #deleteTheme': 'handleDeleteTheme',
			'submit #submitFileEdit': 'submitFileEdit',
			'submit #submitNewFile': 'submitCreateFile',
			'submit #submitNewTheme': 'submitNewTheme',
			'submit #submitDuplicateFile': 'submitDuplicateFile',
			'submit #submitRenameFile': 'submitRenameFile',
			'change #fileType': 'handleFileTypeChange',
			'keydown #fileContent': 'handleSpecialChar'
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

					if (that.package.page.file && that.package.page.location) {
						if (that.package.page.file === 'new') {
							that.editTheme('', true);
						} else {
							that.editTheme(that.package.page.file, false);
						}
					} else if (that.package.page.location) {
						if (that.package.page.location === 'new') {
							that.newTheme();
						} else {
							that.editTheme('index', false);
						}
					} else {
						that.myThemes();
					}
				}
			});
		},
		myThemes: function() {
			this.package.page.title = 'Themes';

			var that = this;
			var getThemes = new Themes();
			getThemes.fetch({
				data: {request: "getThemes"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(ThemesTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(ThemesListTemplate, {data:data}));

					that.global.initView(that);
				}
			});
		},
		activateTheme: function(data) {
			var that = this;
			var themeName = {theme: $(data.currentTarget).data('theme')};
			var activateTheme = new Themes({request: "activateTheme"});

			activateTheme.save(themeName, {
				success: function(model, data) {
					$('.themeBtn button').prop('disabled', false).addClass('activateTheme'); 

					$('.theme').each(function(i,e) {
						if ($(e).data('theme') === data.theme) {
							$(e).find('.themeBtn button').prop('disabled', true).removeClass('activateTheme'); 
						}
					});

					that.global.notice('Success', data.theme + ' has been activated!','on','success',true);
				}
			});

			return false;
		},
		previewTheme: function(data) {
			var that = this;
			var themeName = {theme: $(data.currentTarget).data('theme')};
			var previewTheme = new Themes({request: "previewTheme"});

			previewTheme.save(themeName, {
				success: function(model, data) {
					$('.themeBtn button').prop('disabled', false).addClass('previewTheme'); 

					$('.theme').each(function(i,e) {
						if ($(e).data('theme') === data.previewTheme) {
							$(e).find('.themeBtn button').prop('disabled', true).removeClass('previewTheme'); 
						}
					});

					that.global.notice('Success', data.previewTheme + ' has been activated for preview!','on','success',true);
				}
			});

			return false;
		},
		editTheme: function(file, isNew) {
			var that = this;
			var dir = '';
			var getThemes = new Themes();
			getThemes.fetch({
				data: {
					request: 'getThemeFiles',
					theme: that.package.page.location
				},
				processData: true,
				success: function(model, data) {
					if (isNew) {
						that.package.page.title = 'Create new file for ' + that.package.page.location;
					} else {
						that.package.page.title = that.package.page.location + ' / ' + file;
					}
					that.package.page.themeFiles = data;
					$('.content').append(_.template(ThemesEditTemplate, {data:that.package}));

					$.each(that.package.page.themeFiles, function(i,val) {
						if (val.fileName === file) {
							if (val.type === 'css') {
								dir = 'css';
							} else if (val.type === 'html') {
								dir = 'templates';
							} else if (val.type === 'js') {
								dir = 'views';
							}

							that.package.page.fileDir = dir;
							that.package.page.fileType = val.type;
							file = file + '.' + val.type;
						}
					});
					
					if (isNew) {
						that.newFile();
					} else {
						that.getFile(dir, file);
					}
					that.global.activePage(that.package.page.file);
				}
			});
		},
		newTheme: function() {
			var that = this;
			that.package.page.title = 'Create a new theme';

			var getThemes = new Themes();
			getThemes.fetch({
				data: {request: "getThemes"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(ThemesTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(ThemesCreateThemeTemplate, {data:data}));

					that.global.initView(that);
				}
			});
		},
		submitNewTheme: function(data){
			var that = this;
			var themeData = $(data.currentTarget).serializeObject();
			var themeSubmit = new Themes({request: "saveNewTheme"});
			var themeBtn = $('#submitTheme').button('loading');

			themeSubmit.save(themeData, {
				success: function(model, data) {

					if (data.result) {
						themeBtn.button('reset');
						that.global.notice('Success',data.themeName + ' has been saved.','on','success',true);

						Backbone.history.navigate('themes/'+data.themeName+'/index', {trigger:true});
					} else {
						themeBtn.button('reset');
						that.global.notice('Failed', data.themeName + ' has already been created.','on','danger',true);						
					}
				}
			});

			return false;
		},
		getFile: function(dir, file) {
			var that = this;
			var getThemes = new Themes();
			getThemes.fetch({
				data: {
					request: 'getFile',
					theme: that.package.page.location,
					dir: dir,
					file: file
				},
				processData: true,
				success: function(model, data) {
					var type = file.split('.');
					$('.pageEntry').append(_.template(ThemesEditPageTemplate, {data:data, type:type[1], siteUrl:that.package.environment.siteUrl}));
					$('.modals').append(_.template(ThemesModalsTemplate, {data:that.package, edata: data}));

					that.global.initView(that);
				}
			});
		},
		submitFileEdit: function(data){
			var that = this;
			var fileData = $(data.currentTarget).serializeObject();
			var fileSubmit = new Themes({request: "editFile"});
			var fileBtn = $('#submitEdit').button('loading');

			// Theme Important Info
			fileData.dir = that.package.page.fileDir;
			fileData.file = that.package.page.file + '.' + that.package.page.fileType;
			fileData.fileName = that.package.page.file;
			fileData.pagePermalink = $('#permalink').val();
			fileData.theme = that.package.page.location;

			fileSubmit.save(fileData, {
				success: function(model, data) {
					fileBtn.button('reset');
					that.global.notice('Success',that.package.page.file + ' has been saved.','on','success',true);

					// Update View Page button with new url
					$('.viewPage').attr('href', that.package.environment.siteUrl+'/'+$('#permalink').val());
				}
			});

			return false;
		},
		newFile: function() {
			$('.pageEntry').append(_.template(ThemesCreatePageTemplate, {data:this.package}));
			this.global.initView(this);
		},
		submitCreateFile: function(data) {
			var that = this, type = '';
			var fileData = $(data.currentTarget).serializeObject();
			var fileSubmit = new Themes({request: "saveFile"});
			var fileBtn = $('#submitEdit').button('loading');

			if (fileData.fileType === 'css') {
				fileData.dir = 'css';
			} else if (fileData.fileType === 'html') {
				fileData.dir = 'templates';
			} else if (fileData.fileType === 'js') {
				fileData.dir = 'views';
			}

			fileData.theme = that.package.page.location;
			fileData.file = fileData.fileName + '.' + fileData.fileType;

			fileSubmit.save(fileData, {
				success: function(model, data) {
					Backbone.history.navigate('themes/'+data.theme+'/'+data.fileName, {trigger:true});
				}
			});

			return false;
		},
		submitDuplicateFile: function(data) {
			var that = this;
			var fileData = $(data.currentTarget).serializeObject();
			var fileSubmit = new Themes({request: "duplicateFile"});

			// Theme Important Info
			fileData.dir = that.package.page.fileDir;
			fileData.file = that.package.page.file + '.' + that.package.page.fileType;
			fileData.fileName = that.package.page.file;
			fileData.newFile = fileData.newFileName + '.' + that.package.page.fileType;
			fileData.theme = that.package.page.location;

			fileSubmit.save(fileData, {
				success: function(model, data) {
					$('#duplicateFileModal').modal('hide');
					$('#duplicateFileModal').on('hidden.bs.modal', function () {
						Backbone.history.navigate('themes/'+data.theme+'/'+data.fileName, {trigger:true});
					});
				}
			});

			return false;
		},
		submitRenameFile: function(data) {
			var that = this;
			var fileData = $(data.currentTarget).serializeObject();
			var fileSubmit = new Themes({request: "renameFile"});

			// Theme Important Info
			fileData.dir = that.package.page.fileDir;
			fileData.file = that.package.page.file + '.' + that.package.page.fileType;
			fileData.fileName = that.package.page.file;
			fileData.newFile = fileData.newFileName + '.' + that.package.page.fileType;
			fileData.theme = that.package.page.location;

			fileSubmit.save(fileData, {
				success: function(model, data) {
					$('#renameFileModal').modal('hide');
					$('#renameFileModal').on('hidden.bs.modal', function () {
						Backbone.history.navigate('themes/'+data.theme+'/'+data.fileName, {trigger:true});
					});
				}
			});

			return false;
		},
		handleFileTypeChange: function(data) {
			// Add/Remove Page fields
			var type = $(data.currentTarget).val();
			if (type === 'css' || type === 'js') {
				$('.createPage').hide();
			} else {
				$('.createPage').show();
			}

			// Add/Remove File Comments
			if (type === 'html') {
				$('#fileContent').attr('placeholder','<!-- Add HTML your code here -->');
			} else if (type === 'css') {
				$('#fileContent').attr('placeholder','/* Add your CSS code here */');
			} else if (type === 'js') {
				$('#fileContent').attr('placeholder','// Add your JavaScript code here');
			}
		},
		handleDeleteFile: function() {
			var that = this;
			var fileData = {
				'theme': this.package.page.location,
				'dir': this.package.page.fileType,
				'file': this.package.page.file + '.' + this.package.page.fileType
			};
			var fileDelete = new Themes({request: "deleteFile"});

			if (fileData.dir === 'css') {
				fileData.dir = 'css';
			} else if (fileData.dir === 'html') {
				fileData.dir = 'templates';
			} else if (fileData.dir === 'js') {
				fileData.dir = 'views';
			}

			if ($('#confirmDeleteFile').val().toLowerCase() === 'delete') {
				fileDelete.save(fileData, {
					success: function(model, data) {
						if (data.fileDelete) {
							$('.modal-backdrop').remove();
							Backbone.history.navigate('themes/'+that.package.page.location+'/index', {trigger:true});
						} else {
							that.global.notice('Error','Something went wrong.','on','error',true);
						}
					}
				});
			} else {
				console.log('failed');
			}

			return false;
		},
		handleDeleteTheme: function() {
			var that = this;
			var themeData = {
				'theme': this.package.page.location,
				'dir': this.package.page.fileType,
				'file': this.package.page.file + '.' + this.package.page.fileType
			};
			var themeDelete = new Themes({request: "deleteTheme"});

			if ($('#confirmDeleteTheme').val().toLowerCase() === 'delete') {
				themeDelete.save(themeData, {
					success: function(model, data) {
						if (data.themeDelete) {
							$('.modal-backdrop').remove();
							Backbone.history.navigate('themes', {trigger:true});
						} else {
							that.global.notice('Error','Something went wrong.','on','error',true);
						}
					}
				});
			}

			return false;
		},

		// Text area keypress events
		handleSpecialChar: function(e) {
			// console.log(e.keyCode);
			var textarea = $(e.currentTarget).context;
			if (e.keyCode == 9) {
				// Tab Key
				this.handleCharInsert('\t', textarea);
				e.preventDefault();
			} else if (e.shiftKey && e.keyCode == 57) {
				// Shift + ()
				this.handleCharInsert('()', textarea);
				e.preventDefault();
			} else if (e.shiftKey && e.keyCode == 219) {
				// Shift + Curl Brackets {}
				this.handleCharInsert('{\n\t\n}', textarea);
				e.preventDefault();
			} else if (e.keyCode == 219) {
				// Brackets []
				this.handleCharInsert('[]', textarea);
				e.preventDefault();
			}
		},
		handleCharInsert: function(char, textarea) {
			var startPos = textarea.selectionStart;
			var endPos = textarea.selectionEnd;
			var scrollTop = textarea.scrollTop;
			textarea.value = textarea.value.substring(0, startPos) + char + textarea.value.substring(endPos,textarea.value.length);
			textarea.focus();
			textarea.selectionStart = startPos + char.length;
			textarea.selectionEnd = startPos + char.length;
			textarea.scrollTop = scrollTop;
		}
	});

	return ThemesView;
});