<?php

/*
 *
 * File Name: class.plugins.php
 * Description: Plugin management functionality
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('class.pluginHooks.php');

///// Components /////
$pluginSyncData = array();
$pluginActiveSql = "SELECT * FROM plugins WHERE pluginActive = 1";

$pluginsActive = $connect->query($pluginActiveSql);
while($info = $pluginsActive->fetch_assoc()){
	$pluginName = $info['pluginName'];
	$pluginActive = $info['pluginActive'];
	
	if ($handle = opendir(realpath(__DIR__ . "/../../pyxl-content/plugins/"))) {
		$blacklist = array('.', '..');
		while (false !== ($folder = readdir($handle))) {
			if (!in_array($folder, $blacklist) && $folder == $pluginName && $pluginActive) {

				// Get Theme Info
				$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$folder)."/plugin.php";
				if (file_exists($pluginFile)) {
					include_once($pluginFile);
				}
			}
		}
		closedir($handle);
	}
}
