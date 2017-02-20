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
$connect->query("ALTER TABLE settings ADD siteTimezone VARCHAR(255) NOT NULL AFTER siteEmail;");


// Update Version
$connect->query("UPDATE settings SET version = '0.8'");

?>