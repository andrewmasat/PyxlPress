<?php

/*
 *
 * File Name: class.register.php
 * Description: Registration of new accounts and request activation email sent
 *
 * @param string $username
 * @param string $password
 * @param string $email
 *
 * @throws string result				SUCCESS|USER_EXISTS|EMPTY_FIELD|REGISTRATION_CLOSED
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
$username = $connect->real_escape_string($data->{'username'});
$password = $connect->real_escape_string($data->{'password'});
$email 		= $connect->real_escape_string($data->{'email'});

// Check if Registration is open
$settingsSql = "SELECT * FROM settings";
$envProp = $connect->query($settingsSql);
while($info = $envProp->fetch_assoc()){
	$allowRegister = $info['allowRegister'];
}

if ($allowRegister == 1) {
	if (!empty($username) && !empty($password) && !empty($email)) {

		// username, password, and email are provided so now lets 
		// check if they are in the database.
		$testSql = "SELECT * FROM users WHERE username = '.$username' or email = '$email'";
		$testRecordSet = $connect->query($testSql);
		$existResults = $testRecordSet->num_rows;

		if ($existResults == 0) {
			// Create Salty Passwords
			$salt = md5(uniqid(mt_rand(),true));
			$passwordSalt = $password . $salt;
			$passwordHash = hash("sha256", $passwordSalt);

			$newUserSql = "INSERT INTO users (username,password,salt,level,verified,email,sendEmail,displayName,loginAttemptDate,disabled) 
				VALUES('$username','$passwordHash','$salt',1,0,'$email',1,'$username',NULL,0)";

			$connect->query($newUserSql);
			$userId = $connect->insert_id;
			// Log New Register User (UserId/Username)
			logThis('REGISTER_SUCCESS',$userId.'/'.$username, $connect);

			// Add Notice for new User
			$newNoticeSql = "INSERT INTO notifications (userId,noticeTitle,noticeUrl,noticeIcon, viewed) 
				VALUES('$userId','You created a new account! Update your profile now.','profile/edit','fa-smile-o',0)";

			$connect->query($newNoticeSql);
			$noticeId = $connect->insert_id;
			// Log New Notice (NoticeId/Username)
			logThis('NOTIFICATION_NEW',$noticeId.'/'.$username, $connect);

			// Create Activation 	Code
			$activatePreCode = $email . $salt;
			$activationCode = hash("sha256", $activatePreCode);
			sendEmail('register',$email,$activationCode);

			$data = array(
				'result' => 'SUCCESS',
				'username' => $username,
				'email' => $email
			);

		} else {
			// Log New Register Fail [user exists] (NoticeId/Username)
			logThis('REGISTER_FAILED_USER_EXISTS',$username, $connect);

			$data = array(
				'result' => 'USER_EXISTS'
			);
		}
	} else {
		$data = array(
			'result' => 'EMPTY_FIELD'
		);
	}
} else {
	// Log New Register Fail [user exists] (NoticeId/Username)
	logThis('REGISTER_CLOSED','', $connect);

	$data = array(
		'result' => 'REGISTRATION_CLOSED'
	);
}

// return results
$connect->close();
echo json_encode($data);