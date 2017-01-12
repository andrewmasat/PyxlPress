<?php

/*
 *
 * File Name: class.update.php
 * Description: Update PyxlPress Install
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../general/class.logger.php');
include_once('class.protect.php');


ini_set('max_execution_time',60);

if (protect($_SESSION['username'], $connect)) {
	// Get Current Version
	$versionSql = "SELECT version FROM settings";
	$versionResult = $connect->query($versionSql);

	while($info = $versionResult->fetch_assoc()){
		$currentVersion = $info['version'];
	}
	// Get Latest Version
	$versionUrl = "http://andrewmasat.com/research/versions/version.txt";
	$versionString = file_get_contents($versionUrl);
	$versions = explode(",",$versionString);
	$latestVersion = $versions[0];
	$needUpdate = false;
	$isImportant = false;
	$currentVersionArr = explode('.', $currentVersion);
	$latestVersionArr = explode('.', $latestVersion);

	// Check for update and then determine how important it is.
	if ($latestVersion > $currentVersion) {

		// Log Site Update
		logThis('SITE_UPDATE','Current Version: '.$currentVersion.'/Latest Version: '.$latestVersion, $connect);

		// Check major version
		if ($latestVersionArr[0] > $currentVersionArr[0]) {
			$needUpdate = true;
			$isImportant = true;
		} else if ($latestVersionArr[1] > $currentVersionArr[1]) {
			$needUpdate = true;
			$isImportant = false;
		}
	}

	if ($needUpdate || $_GET['doUpdate'] == true) {
		$doUpdate = false;

		//Download The File If We Do Not Have It
		if (!is_file(realpath(__DIR__ . "/pyxl-core/install/update/pyxlpress-".$latestVersion.".zip"))) {
			
			// Downloading zip
			$newUpdate = file_get_contents("http://andrewmasat.com/research/versions/update/pyxlpress-".$latestVersion.".zip");

			// Log Site Update
			logThis("SITE_UPDATE","Downloading: pyxlpress-".$latestVersion.".zip", $connect);

			// Make folder
			if (!is_dir(realpath(__DIR__ . "/../../pyxl-core/install/update/"))) {
				mkdir(realpath(__DIR__ . "/../../pyxl-core/install")."/update/");
			}

			// Open zip
			$dlHandler = fopen(realpath(__DIR__ . "/pyxl-core/install/update")."/pyxlpress-".$latestVersion.".zip", 'w');

			if (!fwrite($dlHandler, $newUpdate) ) {
				// Failed
				exit();
			}
			
			$doUpdate = true;
			fclose($dlHandler);
		}

		if ($doUpdate == true || $_GET['doUpdate'] == true) {
			// Open zip
			$zipHandle = zip_open(realpath(__DIR__ . "/pyxl-core/install/update")."/pyxlpress-".$latestVersion.".zip");

			while ($aF = zip_read($zipHandle)) {
				$thisFileName = zip_entry_name($aF);
				$thisFileDir = dirname($thisFileName);

				//Continue if its not a file
				if (substr($thisFileName,-1,1) == '/') continue;

				//Make the directory if we need to...
				if (!is_dir (realpath(__DIR__ . "/".$thisFileDir ))) {
					mkdir (realpath(__DIR__) . "/".$thisFileDir );
				}

				//Overwrite the file
				if (!is_dir(realpath(__DIR__ . "/".$thisFileName))) {
					$contents = zip_entry_read($aF, zip_entry_filesize($aF));
					$contents = str_replace("n", "n", $contents);
					$updateThis = '';
				 
					//If we need to run commands, then do it.
					if ($thisFileName == 'class.upgrade.php') {
						$upgradeExec = fopen ('class.upgrade.php','w');
						fwrite($upgradeExec, $contents);
						fclose($upgradeExec);
						include ('class.upgrade.php');
						unlink('class.upgrade.php');
					} else if (strpos($thisFileName, 'error_log') !== false ||
										 strpos($thisFileName, 'class.update.php') !== false) {
						echo '';
					} else {
						$updateThis = fopen(realpath(__DIR__) . "/".$thisFileName, 'w');
						fwrite($updateThis, $contents);
						fclose($updateThis);
						unset($contents);
					}
				}
			}

			// Remove Zip & Folder
			if (!is_dir(realpath(__DIR__ . "/../../pyxl-core/install/update/"))) {
				unlink(realpath(__DIR__ . "/../../pyxl-core/install/update/pyxlpress-".$latestVersion.".zip");
				rmdir(realpath(__DIR__ . "/../../pyxl-core/install/update/");
			}

			// Update version in database
			$versionSql = "UPDATE settings SET version = '$latestVersion'";
			$connect->query($versionSql);

			$data = array(
				'result' => 'success',
				'latestVersion' => $latestVersion
			);
		} else {
			$data = array(
				'result' => 'failed',
				'reason' => 'update already downloaded'
			);
		}
	} else {
		$data = array(
			'result' => 'failed',
			'reason' => 'no update available'
		);
	}
} else {
	$data = array(
		'result' => 'failed',
		'reason' => 'not authorized'
	);
}
echo json_encode($data);
?>