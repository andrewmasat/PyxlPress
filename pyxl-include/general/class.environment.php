<?php
include_once('../config/class.connect.php');
include_once('../plugins/class.pluginInit.php');

// SQL Request
$settingsSql = "SELECT * FROM settings";

$incCoreStyles = true;

$envProp = $connect->query($settingsSql);
while($info = $envProp->fetch_assoc()){
	$allowRegister = $info['allowRegister'];
	$debug = $info['debug'];
	$fixedWidth = $info['fixedWidth'];
	$siteName = $info['siteName'];
	$siteUrl = $info['siteUrl'];
	$theme = $info['theme'];
	$version = $info['version'];
}

$data = array (
	'allowRegister' => $allowRegister,
	'debug' => $debug,
	'fixedWidth' => $fixedWidth,
	'navUrl' => $siteUrl . '/pyxl-core/',
	'siteName' => $siteName,
	'siteUrl' => $siteUrl,
	'version' => $version,
	'year' => date("Y"),
	'theme' => $theme,
	'plugins' => $pluginSyncData
);

// return results
$connect->close();
echo json_encode($data);