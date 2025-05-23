<?php

/*
 *
 * File Name: class.connect.php
 * Description: Connects PyxlPress build to SQL database
 *
 */

// Check if Config exists
if(!file_exists(realpath(__DIR__ . "/../config.php"))) {
	$_SESSION['setupUrl'] = explode("pyxl-core/", $_SERVER['REQUEST_URI'])[0];

	header( "Location: ".$_SESSION['setupUrl']."pyxl-core/install" );
	exit();
} else {
	// mySQL Access
	include_once(realpath(__DIR__ . "/../config.php"));
}

$connect = new mysqli($host, $dbUser, $dbPass, $dbName);
 
// check connection
if ($connect->connect_error) {
	trigger_error("Database connection failed: " . $connect->connect_error, E_USER_ERROR);
}

include_once(realpath(__DIR__ . '/../general/class.time.php'));

session_start();