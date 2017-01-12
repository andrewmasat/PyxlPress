<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$configFile = '../../pyxl-include/config.php';
$configInfo = '
<?php

	///////////////////////////////////////////////////////
	//  Important ! These must be filled in correctly.   //
	// Database details are required to use this script. //
	///////////////////////////////////////////////////////

	$host = "localhost"; // If you don\'t know what your host is, it\'s safe to leave it localhost
	$dbName = "pyxlpress_staging"; // Database name
	$dbUser = "andrewmasat"; // username
	$dbPass = "andpic09"; // Password

	// Session Info
	$attemptLimit = "5"; // Default: 5 Attempts
	$attemptLimitLockout = "300"; // Default: 5 Minutes

	// Timezone
	$timezone = "America/Chicago";

?>';

file_put_contents($configFile, $configInfo);

echo $configFile;
echo $configInfo;

?>