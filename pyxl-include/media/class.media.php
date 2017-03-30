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
	$request = $_GET['request'];
} else if (isset($_GET['files'])) {
	$request = 'uploadMedia';
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

if ($request == 'uploadMedia') {
	if(isset($_GET['files'])) {	
		$media = '';

		$settingsSql = "SELECT * FROM settings";
		$envProp = $connect->query($settingsSql);
		while($info = $envProp->fetch_assoc()){
			$siteUrl = $info['siteUrl'];
		}

		$originDir = '../../pyxl-content/media/uploads/';
		$uploadDirYear = $originDir . date('Y', time()).'/';
		$uploadDirMonth = $uploadDirYear . date('m', time()).'/';
		if (!is_dir($uploadDirYear)) {
			mkdir($uploadDirYear);
		}
		if (!is_dir($uploadDirMonth)) {
			mkdir($uploadDirMonth);
		}

		foreach($_FILES as $file) {
			$fileName = fileExists($file['name'], $uploadDirMonth);

			if(move_uploaded_file($file['tmp_name'], $uploadDirMonth . basename($fileName))) {
				$mediaTitle = $fileName;
				$mediaPermalink = str_replace('../..', $siteUrl, $uploadDirMonth) . $mediaTitle;
				$mediaType = $file['type'];
				$mediaExtension = end(explode('.', $file['name']));
				$mediaSize = $file['size'];

				$mediaSql = "INSERT INTO media (mediaTitle, mediaPermalink, mediaType, mediaExtension, mediaSize) VALUES
										('$mediaTitle', '$mediaPermalink', '$mediaType', '$mediaExtension', '$mediaSize')";
				$connect->query($mediaSql);
				$mediaId = $connect->insert_id;

				$media = array(
					'mediaId' => $mediaId,
					'mediaTitle' => $mediaTitle,
					'mediaPermalink' => $mediaPermalink,
					'mediaType' => $mediaType,
					'mediaExtension' => $mediaExtension,
					'mediaSize' => $mediaSize
				);
			}
		}

		$data = array(
			'uploadMedia' => 'true',
			'media' => $media
		);
		echo json_encode($data);
	}
}

function fileExists($fileName, $filePath) {
	$count = 1;
	if (file_exists($filePath . $filename)) {
		while(!$results) {
			$file = explode('.', $fileName);
			if (!file_exists($filePath . $file[0] . '_' . $count . '.' . $file[1])) {
				$results = $file[0] . '_' . $count . '.' . $file[1];
			}
			$count++;
		}
		return $results;
	} else {
		return $fileName;	
	}
}