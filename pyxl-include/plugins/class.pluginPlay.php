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
				$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$folder)."/plugin.php";
				if (file_exists($pluginFile)) {
					include_once($pluginFile);
				}
			}
		}
		closedir($handle);
	}
}


$settingsSql = "SELECT theme FROM settings";
$themeQuery = $connect->query($settingsSql);
while($info = $themeQuery->fetch_assoc()){
	$themeName = $info['theme'];
}

$templateSql = "SELECT * FROM pages WHERE pageTheme = '$themeName' AND pageFileName = '$request'";
$permalinkQuery = $connect->query($templateSql);
while($info = $permalinkQuery->fetch_assoc()){
	$pageFileName = $info['pageFileName'];
}

$getFile = realpath(__DIR__ . "/../../pyxl-content/themes/".$themeName."/templates")."/".$pageFileName.".html";
$file = file_get_contents($getFile);

$start  = strpos($file, '[[');
$end    = strpos($file, ']]', $start + 2);
$length = $end - $start;
$result = substr($file, $start + 2, $length - 2);

echo $result;