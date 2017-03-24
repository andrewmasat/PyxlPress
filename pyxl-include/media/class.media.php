<?php

/*
 *
 * File Name: class.media.php
 * Description: Media Managament
 *
 */

// Includes
include_once('../config/class.connect.php');

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

if ($request == 'getMedia') {
	$mediaSql = "SELECT * FROM media";
	$mediaQuery = $connect->query($mediaSql);

	$data = array();
	while($info = $mediaQuery->fetch_assoc()){
		$data[] = array(
			'mediaId' => $info['mediaId'],
			'mediaTitle' => $info['mediaTitle'],
			'mediaPermalink' => $info['mediaPermalink'],
			'mediaType' => $info['mediaType'],
			'mediaExtension' => $info['mediaExtension'],
			'mediaSize' => $info['mediaSize']
		);
	}

	$data = array(
		'mediaList' => $data
	);
	echo json_encode($data);
}

if ($request == 'activateTheme') {
	$themeName = $data->{'theme'};

	$themeSql = "UPDATE settings SET theme = '$themeName'";
	$connect->query($themeSql);

	if (isset($_SESSION['previewTheme'])) {
		unset($_SESSION['previewTheme']);
	}

	$data = array(
		'saveSettings' => 'true',
		'theme' => $themeName
	);
	echo json_encode($data);
}
