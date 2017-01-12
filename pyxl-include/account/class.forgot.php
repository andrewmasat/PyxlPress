<?php

/*
 *
 * File Name: class.forgot.php
 * Description: Forgot Password reset
 *
 * @param string $username
 *
 * @return json data
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../general/class.email.php');
include_once('../general/class.logger.php');

// Incoming Data
$data = json_decode(file_get_contents('php://input'));
$username = $connect->real_escape_string($data->{'username'});

if (!empty($username)) {
	// Salt extraction
	$saltSql = "SELECT salt, email FROM users WHERE username = '$username' OR email = '$username'";
	$saltRecordSet = $connect->query($saltSql);
	while($info = $saltRecordSet->fetch_assoc()){
		$salt = $info['salt'];
		$email = $info['email'];
	}

	if (isset($salt)) {
		// Cook Salted Code
		$saltedCode =  $email . $salt;
		$hashedCode = hash('sha256', $saltedCode);
	
		sendEmail('forgotPasswordReset',$email,$hashedCode);
	}


	$data = array(
		'email' => $email
	);
} else {
	$data = array(
		'result' => 'MISSING_USERNAME'
	);
	die(json_encode($data));
}

// return results
$connect->close();
echo json_encode($data);