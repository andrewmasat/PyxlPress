<?php

function siteDateTime($timestamp, $connect, $siteFirst = true) {

	if ($timestamp !== NULL) {

		// Set Timezone
		$settingsSql = "SELECT * FROM settings";

		$envProp = $connect->query($settingsSql);
		while($info = $envProp->fetch_assoc()){
			$siteTimezone = $info['siteTimezone'];
			$siteTimeFormat = $info['siteTimeFormat'];
		}

		if (isset($_SESSION['username']) && !$siteFirst) {
			$username = $_SESSION['username'];
			$settingsSql = "SELECT timezone FROM users WHERE username = '$username'";

			$envProp = $connect->query($settingsSql);
			while($info = $envProp->fetch_assoc()){
				$siteTimezone = $info['timezone'];
			}
		}

		$d = new DateTime($timestamp);
		$d->setTimeZone(new DateTimeZone($siteTimezone));

		return $d->format($siteTimeFormat);
	} else {
		return "NO_TIMESTAMP";
	}
}

function siteCustomTime($timestamp, $format, $connect) {
	// Set Timezone
	$settingsSql = "SELECT * FROM settings";

	$envProp = $connect->query($settingsSql);
	while($info = $envProp->fetch_assoc()){
		$siteTimezone = $info['siteTimezone'];
	}

	$d = new DateTime($timestamp);
	$d->setTimeZone(new DateTimeZone($siteTimezone));

	return $d->format($format);
}