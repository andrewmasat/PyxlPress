<?php

/*
 *
 * File Name: class.logout.php
 * Description: Logout and kill session
 *
 * @param session username
 *
 * @throws string logout		SUCCESS|NO_SESSION
 *
 * @return json data
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../general/class.logger.php');

if (isset($_SESSION['username'])) {
	// Log (Successful Activation)
	logThis('LOGOUT_SUCCESS',$_SESSION['username'], $connect);

	session_unset();
	session_destroy();

	$data = array(
		'logout' => 'SUCCESS'
	);
} else {
	$data = array(
		'logout' => 'NO_SESSION'
	);
}

// return results
echo json_encode($data);