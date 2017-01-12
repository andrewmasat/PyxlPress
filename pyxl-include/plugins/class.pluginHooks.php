<?php

/*
 *
 * File Name: class.pluginHooks.php
 * Description: Repository of plugin hooks
 *
 */

function trigger_hook_init($hookData) {
	global $pluginSyncData;

	$pluginSyncData[] = $hookData;
	return $pluginSyncData;
};

function admin_create_page($pluginName, $pageTitle = '') {
	$hookData = array (
		'hookType' => 'admin_create_page',
		'pluginName' => $pluginName,
		'pageTitle' => $pageTitle
	);

	trigger_hook_init($hookData);
};

function admin_sidebar_menu(
	$menuTitle = 'Plugin button broken',
	$menuUrl = '',
	$menuIcon = '',
	$menuLevel = 0,
	$pluginSecLevel = '3'
) {
	if ($menuUrl == 'plugins') {
		$menuUrl = 'duplicate'.$menuUrl;
	}

	$hookData = array (
		'hookType' => 'admin_sidebar_menu',
		'menuIcon' => $menuIcon,
		'menuLevel' => $menuLevel,
		'menuTitle' => $menuTitle,
		'menuUrl' => $menuUrl,
		'pluginSecLevel' => $pluginSecLevel
	);
	
	trigger_hook_init($hookData);
};

function create_view($pluginName, $viewUrl = 'views/indexView.js') {
	$hookData = array (
		'hookType' => 'create_view',
		'pluginName' => $pluginName,
		'viewUrl' => $viewUrl
	);
	
	trigger_hook_init($hookData);
};

function sql_query($pluginName, $query, $connect) {
	$pluginSql = $query;
	// $connect->query($pluginSql);

	$data = array(
		'pluginName' => $pluginName,
		'pluginSql' => $pluginSql
	);
	echo json_encode($data);
};