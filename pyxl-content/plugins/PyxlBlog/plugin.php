<?php
/// PyxlBlog - v0.1 ///
// by:  Andrew Masat //
$pluginTitle				= "PyxlBlog";
$pluginDescription 	= "Add Blog and Article creation to your PyxlPress site.";
$pluginSecLevel			= "3";
$pluginUrl					= "PyxlBlog";
$pluginVersion			= "0.1";

// Installation
$installSql = "CREATE TABLE IF NOT EXISTS pb_posts (
								pb_id INT(11) NOT NULL AUTO_INCREMENT,
								pb_author VARCHAR(50) NOT NULL,
								pb_title VARCHAR(255) NOT NULL,
								pb_content LONGTEXT NOT NULL,
								pb_permalink VARCHAR(1000) NOT NULL,
								pb_status INT(1) NOT NULL,
								pb_remove INT(1) NOT NULL,
								pb_update TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL DEFAULT NULL,
								pb_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
								PRIMARY KEY (pb_id)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Uninstallation
$uninstallSql = "DROP TABLE pb_posts;";

// Adding a simple menu button into the admin sidebar menu
admin_sidebar_menu('Blog', $pluginUrl, 'fa-font');
// Adding Submenu buttons to the origin button
admin_sidebar_menu('New Post', $pluginUrl.'/new', 'fa-plus', 1);
admin_sidebar_menu('Blog Settings', $pluginUrl.'/settings', 'fa-gear', 1);

// Create View
create_view($pluginUrl, 'views/indexView.js');

// Create admin page
admin_create_page($pluginUrl, 'PyxlBlog');

?>