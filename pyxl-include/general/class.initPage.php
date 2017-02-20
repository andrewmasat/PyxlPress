<?php
	include_once(realpath(__DIR__ . "/../config/class.connect.php"));

	// SQL Request
	$settingsSql = "SELECT * FROM settings";
	$incCoreStyles = true;

	$envProp = $connect->query($settingsSql);
	while($info = $envProp->fetch_assoc()){
		$fixedWidth = $info['fixedWidth'];
		$siteName = $info['siteName'];
		$siteUrl = $info['siteUrl'];
		$siteTimezone = $info['siteTimezone'];
		if (isset($_SESSION['previewTheme'])) {
			$theme = $_SESSION['previewTheme'];
		} else {
			$theme = $info['theme'];
		}
	}
?>