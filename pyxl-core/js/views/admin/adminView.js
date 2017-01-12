define([
	'jquery',
	'underscore',
	'backbone',
	'models/general/environment',
	'models/general/enforcer',
	'models/admin/admin',
	'models/admin/update',
	'views/global/globalView',
	'text!templates/home/page.html',
	'text!templates/general/header.html',
	'text!templates/general/footer.html',
	'text!templates/general/sidebar.html',
	'text!templates/admin/adminTemplate.html',
	'text!templates/admin/adminViewTemplate.html',
	'text!templates/admin/adminSettingsTemplate.html',
	'text!templates/admin/adminUsersTemplate.html',
	'text!templates/admin/adminEditUserTemplate.html',
	'text!templates/admin/adminNewUserTemplate.html',
	'text!templates/admin/adminRolesTemplate.html',
	'text!templates/admin/adminUpdateTemplate.html',
	'text!templates/admin/adminUpdateCompleteTemplate.html'
	], function($, _, Backbone, Environment, Enforcer, Admin, Update, GlobalEvents, 
				Page, Header, Footer, Sidebar, AdminTemplate, AdminViewTemplate,
				AdminSettingsTemplate, AdminUserListTemplate, AdminEditUserTemplate,
				AdminNewUserTemplate, AdminRolesTemplate,	AdminUpdateTemplate,
				AdminUpdateCompleteTemplate){

	'use strict';

	// Views
	var AdminView = Backbone.View.extend({
		el: $('.stage'),
		events: {
			'submit #submitSettingsPage': 'submitSettingsPage',
			'submit #editUser': 'submitEditUser',
			'submit #submitRoles': 'submitEditRole',
			'click .installUpdate': 'installUpdate'
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

					if (that.package.page.location === 'settings') {
						that.editSettingsPage();
					} else if (that.package.page.location === 'update') {
						that.updatePage();
					} else if (that.package.page.location === 'users') {
						// If there is a user ID then show that ID's edit page
						// Else show the users list
						if (that.package.page.id) {
							that.editUserPage();
						} else {
							that.UserListPage();
						}
					} else if (that.package.page.location === 'roles') {
						that.editRolesPage();
					} else {
						that.adminPage();
					}
				}
			});
		},
		adminPage: function() {
			this.package.page.title = 'Site Health';

			var that = this;
			var getAdmin = new Admin();
			getAdmin.fetch({
				data: {request: "getHealth"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(AdminTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(AdminViewTemplate, {data:data}));
					
					that.global.initView(that);
					that.global.activePage('health');
				}
			});
		},
		editSettingsPage: function() {
			this.package.page.title = 'Edit Site Settings';

			var that = this;
			var getAdmin = new Admin();
			getAdmin.fetch({
				data: {request: "getSettings"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(AdminTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(AdminSettingsTemplate, {data:data}));
					
					that.global.initView(that);
					that.global.activePage('settings');
				}
			});
		},
		submitSettingsPage: function(data) {
			var that = this;
			var settingData = $(data.currentTarget).serializeObject();
			var settingSubmit = new Admin({request: "saveSettings"});
			var settingBtn = $('#submitEdit').button('loading');

			$(':checkbox').each(function() {
				if(!$(this).is(':checked')) {
					var name = $(this).attr('name');
					settingData[name] = '0';
				}
			});

			settingSubmit.save(settingData, {
				success: function(model, data) {
					settingBtn.button('reset');
					that.global.notice('Success','Site settings has been saved.','on','success',true);
					location.reload();
				}
			});

			return false;
		},
		UserListPage: function() {
			this.package.page.title = 'Site Users';

			var that = this;
			var getAdmin = new Admin();
			getAdmin.fetch({
				data: {request: "getUserList"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(AdminTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(AdminUserListTemplate, {data:data}));
					
					that.global.initView(that);
					that.global.activePage('users');
				}
			});
		},
		editUserPage: function() {
			var that = this;

			if (this.package.page.id === 'new') {
				this.package.page.title = 'Create New User';

				$('.content').append(_.template(AdminTemplate, {data:this.package}));
				$('.pageEntry').append(_.template(AdminNewUserTemplate, {data:this.package}));
			} else {
				var getAdmin = new Admin();
				getAdmin.fetch({
					data: {request: "getUser", id: that.package.page.id},
					processData: true,
					success: function(model, data) {
						that.package.page.title = 'Edit ' + data.username;
						data.siteUrl = that.package.environment.siteUrl;

						$('.content').append(_.template(AdminTemplate, {data:that.package}));
						$('.pageEntry').append(_.template(AdminEditUserTemplate, {data:data}));
						
						that.global.initView(that);
						that.global.activePage('users');
					}
				});
			}
		},
		submitEditUser: function(data) {
			var that = this;
			var userData = $(data.currentTarget).serializeObject();
			var userSubmit = new Admin({request: "saveUser", id: that.package.page.id});
			var userBtn = $('#submitEdit').button('loading');

			$(':checkbox').each(function() {
				var name = $(this).attr('name');
				if(!$(this).is(':checked')) {
					userData[name] = '0';
				} else {
					userData[name] = '1';
				}
			});

			userSubmit.save(userData, {
				success: function(model, data) {
					userBtn.button('reset');
					that.global.notice('Success','User has been updated.','on','success',true);
				}
			});

			return false;
		},
		editRolesPage: function() {
			this.package.page.title = 'Site Roles';

			var that = this;
			var getAdmin = new Admin();
			getAdmin.fetch({
				data: {request: "getRoles"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(AdminTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(AdminRolesTemplate, {data:data}));
					
					that.global.initView(that);
					that.global.activePage('roles');
				}
			});
		},
		submitEditRole: function(data) {
			var that = this;
			var roleData = $(data.currentTarget).serializeObject();
			var roleSubmit = new Admin({request: "saveRoles"});
			var roleBtn = $('#submitEdit').button('loading');

			roleSubmit.save(roleData, {
				success: function(model, data) {
					roleBtn.button('reset');
					that.global.notice('Success','Roles has been updated.','on','success',true);
				}
			});

			return false;
		},
		updatePage: function() {
			this.package.page.title = 'Updates';

			var that = this;
			var getAdmin = new Admin();
			getAdmin.fetch({
				data: {request: "getHealth"},
				processData: true,
				success: function(model, data) {
					$('.content').append(_.template(AdminTemplate, {data:that.package}));
					$('.pageEntry').append(_.template(AdminUpdateTemplate, {data:data}));
					
					that.global.initView(that);
					that.global.activePage('update');
				}
			});
		},
		installUpdate: function() {
			var that = this;
			var getUpdate = new Update();
			getUpdate.fetch({
				data: {doUpdate: true},
				processData: true,
				beforeSend: function() {
					$('.pageEntry').html('<p>Beginning update....</p>');
				},
				success: function(model, data) {
					$('.pageEntry').html(_.template(AdminUpdateCompleteTemplate, {data:data}));
				}
			});
		}
	});

	return AdminView;
});