<?php

/*
 *
 * File Name: class.upgrade.php
 * Description: Install scripts
 *
 */

// Includes
include_once(realpath(__DIR__ . '/../../pyxl-include/config/class.connect.php'));


// v0.8
// Timezone added to settings
$connect->query("ALTER TABLE settings ADD siteTimeFormat VARCHAR(255) NOT NULL AFTER siteEmail;");
$connect->query("UPDATE settings SET siteTimeFormat = 'm/d/y g:i a'");
$connect->query("ALTER TABLE settings ADD siteTimezone VARCHAR(255) NOT NULL AFTER siteTimeFormat;");
$connect->query("UPDATE settings SET siteTimezone = 'America/Chicago'");

// Timezone added to user
$connect->query("ALTER TABLE users ADD timezone VARCHAR(255) NOT NULL AFTER sendEmail;");
$connect->query("UPDATE users SET timezone = 'America/Chicago'");


// Update Version
$connect->query("UPDATE settings SET version = '0.8'");

?>