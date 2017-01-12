<?php

/*
 *
 * File Name: class.activate.php
 * Description: Activates the account of user
 *
 * @param string $tokenId				ID is hashed during registration and sent via email
 * @param string $tokenEmail		Email is needed to compare tokenId to correct hashed information
 *
 * @throws string result				SUCCESS|FAILED
 * @throws string reason				BAD_CODE|NO_USER|MISSING_TOKEN
 *
 * @return json data
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../general/class.email.php');
include_once('../general/class.logger.php');

// Incoming Data
$tokenId 	= $connect->real_escape_string($_GET['tokenId']);
$tokenEmail = $connect->real_escape_string($_GET['tokenEmail']);

if (!empty($tokenId) && !empty($tokenEmail)) {

	// Check if user is real.
	$userSql = "SELECT * FROM users WHERE email = '$tokenEmail'";
	$testRecordSet = $connect->query($userSql);
	$existResults = $testRecordSet->num_rows;

	if ($existResults > 0) {
		while($info = $testRecordSet->fetch_assoc()){
			$email = $info['email'];
			$salt  = $info['salt'];
		}
	
		$activatePreCode = $email . $salt;
		$activationCode = hash("sha256", $activatePreCode);

		if ($tokenId == $activationCode) {
			$activateSql = "UPDATE users SET verified = 1 WHERE email = '$email'";
			$connect->query($activateSql);

			// Log Successful Activation (email address)
			logThis('ACTIVATE_SUCCESS',$email, $connect);

			$data = array(
				'result' => 'SUCCESS'
			);
		} else {
			// Log Failed Activation [bad code] (email address)
			logThis('ACTIVATE_FAILED_BAD_CODE',$email, $connect);

			$data = array(
				'result' => 'FAILED',
				'reason' => 'BAD_CODE'
			);
		}
	} else {
		// Log Failed Activation [no user] (token)
		logThis('ACTIVATE_FAILED_NO_USER',$tokenEmail, $connect);

		$data = array(
			'result' => 'FAILED',
			'reason' => 'NO_USER'
		);
	}
} else {
	// Log Failed Activation [no token] (NULL)
	logThis('ACTIVATE_FAILED_MISSING_TOKEN','', $connect);

	$data = array(
		'result' => 'FAILED',
		'reason' => 'MISSING_TOKEN'
	);
}

// return results
$connect->close();
echo json_encode($data);