<?php

/*
 *
 * File Name: class.resetPassword.php
 * Description: Resetting user password
 *
 * @param string $password
 * @param string $confirmPassword
 *
 * @return json data
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../general/class.email.php');
include_once('../general/class.logger.php');

// Incoming Data
$data 		= json_decode(file_get_contents('php://input'));
$email = $connect->real_escape_string($data->{'email'});
$forgotId = $connect->real_escape_string($data->{'forgotId'});
$newpassword = $connect->real_escape_string($data->{'password'});
$confirmPassword = $connect->real_escape_string($data->{'confirmPassword'});

if (!empty($email) && !empty($newpassword) && !empty($confirmPassword) && !empty($forgotId)) {

	$testSql = "SELECT * FROM users WHERE email = '$email'";
	$testRecordSet = $connect->query($testSql);
	$existResults = $testRecordSet->num_rows;
	while($info = $testRecordSet->fetch_assoc()){
		$salt = $info['salt'];
		$username = $info['username'];
	}

	$samePass = 1;
	if ($newpassword !== $confirmPassword) {
		$samePass = 0;
	}

	if ($existResults == 1 && $samePass == 1) {
		// Cook Salted Code
		$saltedCode =  $email . $salt;
		$hashedCode = hash('sha256', $saltedCode);

		if ($hashedCode == $forgotId) {
			$newSalt = md5(uniqid(mt_rand(),true));
			$passwordSalt = $newpassword . $newSalt;
			$passwordHash = hash("sha256", $passwordSalt);
	
			$insertSql = "UPDATE users SET password = '$passwordHash', salt = '$newSalt' WHERE username = '$username'";
			$connect->query($insertSql);

			sendEmail('forgotPasswordComplete',$email,$hashedCode);
			
			$data = array(
				'save' => 'success'
			);
		} else {
			// Log New Register Fail [user exists] (NoticeId/Username)
			logThis('PASSWORD_RESET_FAILED_BAD_FORGETID',$email, $connect);

			$data = array(
				'result' => 'BAD_FORGETID'
			);
		}
	} else {
		if ($samePass == 0) {
			// Password reset failed - No User
			logThis('PASSWORD_RESET_FAILED_BAD_PASSWORD',$email, $connect);

			$data = array(
				'result' => 'BAD_PASSWORD'
			);
		} else {
			// Password reset failed - No User
			logThis('PASSWORD_RESET_FAILED_NO_USER',$email, $connect);

			$data = array(
				'result' => 'NO_USER'
			);
		}
	}
} else {
	$data = array(
		'result' => 'EMPTY_FIELD'
	);
}


// return results
$connect->close();
echo json_encode($data);