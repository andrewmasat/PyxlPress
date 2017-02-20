<?php

function siteDateTime($timestamp, $connect) {

	if ($timestamp !== NULL) {
		// Set Timezone
		$settingsSql = "SELECT * FROM settings";

		$envProp = $connect->query($settingsSql);
		while($info = $envProp->fetch_assoc()){
			$siteTimezone = $info['siteTimezone'];
			$siteTimeFormat = $info['siteTimeFormat'];
		}

		$d = new DateTime($timestamp);
		$d->setTimeZone(new DateTimeZone($siteTimezone));

		return $d->format($siteTimeFormat);
	} else {
		return "NO_UPDATE";
	}
}