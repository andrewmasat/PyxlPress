<?php

/*
 *
 * File Name: class.plugins.php
 * Description: Plugin management functionality
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../admin/class.protect.php');
include_once('class.pluginHooks.php');

//  Get Request
if (isset($_GET['request'])) {
	$request = $_GET['request'];
} else {
	$data = json_decode(file_get_contents('php://input'));
	$request = $data->{'request'};
}

// Get username
if (isset($_SESSION['username']) && $request != 'triggerHook') {
	$username = $connect->real_escape_string($_SESSION['username']);

	if ($request == 'getPluginList') {
		$data = array();
		
		if ($handle = opendir(realpath(__DIR__ . "/../../pyxl-content/plugins/"))) {
			$blacklist = array('.', '..');
			while (false !== ($folder = readdir($handle))) {
				$pluginActive = false;
				$pluginInstalled = false;
				$pluginActiveSql = "SELECT * FROM plugins WHERE pluginName = '$folder'";
				$pluginsActive = $connect->query($pluginActiveSql);
				while($info = $pluginsActive->fetch_assoc()){
					if ($info['pluginName']) {
						$pluginInstalled = true;
					}
					if ($info['pluginActive']) {
						$pluginActive = true;
					}
				}

				if (!in_array($folder, $blacklist)) {

					// Get Theme Info
					$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$folder)."/plugin.php";
					if (file_exists($pluginFile)) {
						// Default Vars
						$pluginDescription = '';
						$pluginSecLevel = 0;
						$pluginUrl = '';
						$pluginVersion = '';

						// Open Plugin File
						include_once($pluginFile);

						$data[] = array(
							'pluginActive' => $pluginActive,
							'pluginDescription' => $pluginDescription,
							'pluginInstalled' => $pluginInstalled,
							'pluginName' => $folder,
							'pluginSecLevel' => $pluginSecLevel,
							'pluginUrl' => $pluginUrl,
							'pluginVersion' => $pluginVersion
						);
					}
				}
			}
			closedir($handle);
		}
		
		echo json_encode($data);
	}

	if ($request == 'getPlugin') {
		$pluginName = $_GET['pluginName'];

		$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$pluginName)."/plugin.php";
		if (file_exists($pluginFile)) {
			// Open Plugin File
			include_once($pluginFile);
			
		}

		$data = array(
			'pluginLocation' => $pluginName
		);
		echo json_encode($data);
	}
	
	if ($request == 'activatePlugin') {
		$pluginName = $data->{'pluginName'};
		$pluginSecLevel = $data->{'pluginSecLevel'};
		$pluginUrl = $data->{'pluginUrl'};
		$pluginVersion = $data->{'pluginVersion'};

		$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$pluginName)."/plugin.php";
		if (file_exists($pluginFile)) {
			// Open Plugin File
			include_once($pluginFile);
		}

		$testSql = "SELECT * FROM plugins WHERE pluginName = '$pluginName'";
		$testRecordSet = $connect->query($testSql);
		$existResults = $testRecordSet->num_rows;

		if ($existResults == 0) {
			$pluginSql = "INSERT INTO plugins (pluginName, pluginActive, pluginVersion) VALUES ('$pluginName', 1, '$pluginVersion')";
			
			$securitySql = "INSERT INTO security (location, level, active) VALUES ('$pluginUrl', $pluginSecLevel, 1)";

			// Install SQL if available
			if (isset($installSql)) {
				$connect->query($installSql);
			}
		} else {
			$pluginSql = "UPDATE plugins SET pluginActive = 1, pluginVersion = '$pluginVersion' WHERE pluginName = '$pluginName'";

			$securitySql = "UPDATE security SET active = 1 WHERE location = '$pluginUrl'";
		}
		$connect->query($pluginSql);
		$connect->query($securitySql);

		$data = array(
			'pluginActive' => 'true',
			'pluginName' => $pluginName
		);
		echo json_encode($data);
	}
	
	if ($request == 'deactivatePlugin') {
		$pluginName = $data->{'pluginName'};
		$pluginUrl = $data->{'pluginUrl'};

		$pluginSql = "UPDATE plugins SET pluginActive = 0 WHERE pluginName = '$pluginName'";
		$securitySql = "UPDATE security SET active = 0 WHERE location = '$pluginUrl'";
		$connect->query($pluginSql);
		$connect->query($securitySql);

		$data = array(
			'pluginActive' => 'false',
			'pluginName' => $pluginName
		);
		echo json_encode($data);
	}

	if ($request == 'uninstallPlugin') {
		$pluginName = $data->{'pluginName'};
		$pluginUrl = $data->{'pluginUrl'};

		$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$pluginName)."/plugin.php";
		if (file_exists($pluginFile)) {
			// Open Plugin File
			include_once($pluginFile);
		}

		// Uninstall SQL if available
		if (isset($uninstallSql)) {
			$connect->query($uninstallSql);
		}

		$pluginSql = "DELETE FROM plugins WHERE pluginName = '$pluginName'";
		$securitySql = "DELETE FROM security WHERE location = '$pluginUrl'";
		$connect->query($pluginSql);
		$connect->query($securitySql);

		$data = array(
			'pluginDelete' => 'true',
			'pluginName' => $pluginName
		);
		echo json_encode($data);
	}
} else if ($request == 'triggerHook') {
		$hookData = $data->{'hookData'};
		$hookType = $data->{'hookType'};
		$pluginName = $data->{'pluginName'};
		$areYouSecure = false;

		if (isset($_SESSION['username'])) {
			$areYouSecure = protect($_SESSION['username'], $connect);
		}

		$pluginFile = realpath(__DIR__ . "/../../pyxl-content/plugins/".$pluginName)."/functions/functions.php";
		if (file_exists($pluginFile)) {
			// Open Plugin File
			include_once($pluginFile);
		}
		if (function_exists($hookType)) {
			$hookType($pluginName, $hookData, $areYouSecure, $connect);
		}
} else {
	$data = array(
		'loggedin' => 'false'
	);
	echo json_encode($data);
	die;
}