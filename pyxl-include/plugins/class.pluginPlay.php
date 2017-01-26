<?php

/*
 *
 * File Name: class.pluginPlay.php
 * Description: Plugin And Play in themes
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('class.pluginHooks.php');

if (isset($_GET['request'])) {
	if (empty($_GET['request'])) {
		$request = 'index';
	} else {
		$request = $_GET['request'];
	}
} else {
	$data = json_decode(file_get_contents('php://input'));
	$request = $data->{'request'};
}

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
				$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$folder)."/functions/functions.php";
				if (file_exists($pluginFile)) {
					include_once($pluginFile);

					$request = explode(',',$request);
					$hook = 'get_hook_' . $request[0];
					
					if (function_exists($hook)) {
						array_unshift($request, $folder);
						$hook($request, $connect);
					} else {
						$data = 'Hook Not Found';
						echo json_encode($data);
					}

				}
			}
		}
		closedir($handle);
	}
}
