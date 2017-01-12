<?php

/*
 *
 * File Name: class.upgrade.php
 * Description: Install scripts
 *
 */

// Includes
include_once(realpath(__DIR__ . '/../../pyxl-include/config/class.connect.php'));


// v0.7
// Add/Set fixed Width for Admin
$connect->query("ALTER TABLE pages ADD pageTheme VARCHAR(255) NOT NULL;");

// Change notice read to viewed
$connect->query("ALTER TABLE notifications CHANGE read viewed INT(1) NOT NULL;");

// Change security table and add active column
$connect->query("ALTER TABLE security ADD active INT(1) NOT NULL AFTER level;");
$connect->query("UPDATE security SET active = 1;");

// Add plugins page
$connect->query("INSERT INTO security (securityId, location, level, active) VALUES (NULL, 'plugins', 3, 1);");
// Add Welcome page
$connect->query("INSERT INTO security (securityId, location, level, active) VALUES (NULL, 'welcome', 1, 1);");

$connect->query("CREATE TABLE IF NOT EXISTS plugins (
	pluginId int(11) NOT NULL AUTO_INCREMENT,
	pluginName varchar(255) NOT NULL,
	pluginActive int(1) NOT NULL,
	pluginVersion varchar(255) NOT NULL,
	PRIMARY KEY (pluginId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Update Version
$connect->query("UPDATE settings SET version = '0.7'");

?>