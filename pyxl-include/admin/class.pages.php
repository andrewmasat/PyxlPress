<?php

/*
 *
 * File Name: class.pages.php
 * Description: Page creation/management functionality
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('class.protect.php');


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
if ($request == 'getPages' && protect($_SESSION['username'], $connect)) {
	$pagesSql = "SELECT * FROM pages";
	$pages = $connect->query($pagesSql);

	// SQL Request
	$data = array();
	while($info = $pages->fetch_assoc()){
		$data[] = array (
			'pageId' => $info['pageId'],
			'pagePermalink' => $info['pagePermalink'],
			'pageFileName' => $info['pageFileName']
		);
	}
} else {
	$theme = '';
	$themeSql = "SELECT * FROM settings";

	$getTheme = $connect->query($themeSql);
	while($info = $getTheme->fetch_assoc()){
		if (isset($_SESSION['previewTheme'])) {
			$theme = $_SESSION['previewTheme'];
		} else {
			$theme = $info['theme'];
		}
	}
	// Front page Page requesting
	$pagesSql = "SELECT * FROM pages WHERE pageTheme = '$theme' AND pagePermalink = '$request'";

	$pages = $connect->query($pagesSql);
	$pageResult = $pages->num_rows;
	if ($pageResult === 0) {
		$pagesSql = "SELECT * FROM pages WHERE pageId = 1";
		$pages = $connect->query($pagesSql);
		$pageAvalible = true;
	} else {
		$pageAvalible = false;
	}

	// SQL Request
	while($info = $pages->fetch_assoc()){
		$pageId = $info['pageId'];
		$pagePermalink = $info['pagePermalink'];
		$pageFileName = $info['pageFileName'];
	}

	// Check if Plugin Hook is there
	$getFile = realpath(__DIR__ . "/../../pyxl-content/themes/".$theme."/templates")."/".$pageFileName.".html";
	$file = file_get_contents($getFile);
	$pluginHooks = get_string_between($file, 'data-plugin="', '"');

	// Return data
	$data = array (
		'pageId' => $pageId,
		'pagePermalink' => $pagePermalink,
		'pageAvalible' => $pageAvalible,
		'pageFileName' => $pageFileName,
		'theme' => $theme,
		'pluginHooks' => $pluginHooks
	);
}

// return results
$connect->close();
echo json_encode($data);


function get_string_between($str, $startDelimiter, $endDelimiter) {
  $contents = array();
  $startDelimiterLength = strlen($startDelimiter);
  $endDelimiterLength = strlen($endDelimiter);
  $startFrom = $contentStart = $contentEnd = 0;
  while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
    $contentStart += $startDelimiterLength;
    $contentEnd = strpos($str, $endDelimiter, $contentStart);
    if (false === $contentEnd) {
      break;
    }
    $contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
    $startFrom = $contentEnd + $endDelimiterLength;
  }

  return $contents;
}