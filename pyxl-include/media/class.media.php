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
	} else if (isset($_GET['files'])) {
		$request = 'uploadMedia';
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

if ($request == 'uploadMedia') {
	if(isset($_GET['files'])) {	
		$media = array();

		// $uploaddir = '../../pyxl-content/media/uploads/'.$date('Y/m', time()).'/';
		$uploaddir = '../../pyxl-content/media/uploads/';
		if (!is_dir($uploaddir)) {
			mkdir($uploaddir);
		}
		foreach($_FILES as $file) {
			if(move_uploaded_file($file['tmp_name'], $uploaddir .basename($file['name']))) {
				$mediaTitle = $file['name'];
				$mediaPermalink = $uploaddir . $mediaTitle;
				$mediaType = $file['type'];
				$mediaExtension = end(explode('.', $file['name']));
				$mediaSize = $file['size'];

				$mediaSql = "INSERT INTO media (mediaTitle, mediaPermalink, mediaType, mediaExtension, mediaSize) VALUES
										('$mediaTitle', '$mediaPermalink', '$mediaType', '$mediaExtension', '$mediaSize')";
				$connect->query($mediaSql);
				$mediaId = $connect->insert_id;

				$media[] = array(
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
