<?php

/*
 *
 * File Name: class.login.php
 * Description: Login and session setting
 *
 * @param string $username
 * @param string $password
 *
 * @throws string result				EMPTY_FIELD|HULK_SMASH|BAD_LOGIN
 * @throws string attempt				Login attempt count
 *
 * @return json data
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../general/class.logger.php');

// Incoming Data
$data = json_decode(file_get_contents('php://input'));
$username = $connect->real_escape_string($data->{'username'});
$password = $connect->real_escape_string($data->{'password'});

if (!empty($username) && !empty($password)) {
	// Salt extraction & Hulk attempt
	$saltSql = "SELECT salt, loginAttempt, loginAttemptDate FROM users WHERE username = '$username' OR email = '$username'";
	$saltRecordSet = $connect->query($saltSql);
	while($info = $saltRecordSet->fetch_assoc()){
		$salt = $info['salt'];
		$attempt = $info['loginAttempt'];
		$attemptDate = $info['loginAttemptDate'];
	}

	if (isset($salt)) {
		// Cook Salted Password
		$saltedPW =  $password . $salt;
		$hashedPW = hash('sha256', $saltedPW);

		// Test Salted Passroll
		$testSql = "SELECT * FROM users WHERE (username = '$username' OR email = '$username') AND password = '$hashedPW'";
		$testRecordSet = $connect->query($testSql);
		$testResult = $testRecordSet->num_rows;

		// Hulk attack?
		$curtime = time();
		$attemptTime = strtotime($attemptDate);
		if ($attempt > $attemptLimit && ($curtime - $attemptTime) < $attemptLimitLockout) {
			$hulkSmash = true;

			// Log Login Attempt Reset(Username/AttemptDate)
			logThis('LOGIN_ATTEMPT_HULKATTACK',$username.'/'.$attemptDate, $connect);
		} else {
			$hulkSmash = false;

			// Add Login Attempt
			$attempt = $attempt + 1;
			$attemptTimestamp = date("Y-m-d H:i:s");
			$attemptSql = "UPDATE users SET loginAttempt = $attempt, loginAttemptDate = '$attemptTimestamp' WHERE username = '$username' OR email = '$username'";
			$connect->query($attemptSql);

			// Log Login Fail Attempt(Username/AttemptCount)
			logThis('LOGIN_ATTEMPT',$username.'/'.$attempt, $connect);
		}
	} else {
		$hulkSmash = false;
		$testResult = 0;

		// Log Login Failed [no account] (Username)
		logThis('LOGIN_FAILED_NO_ACCOUNT',$username, $connect);
	}

	// Pass or fail?
	if ($testResult > 0 && !$hulkSmash) {
		// Reset Attempt counter
		$attemptSql = "UPDATE users SET loginAttempt = 0 WHERE username = '$username' OR email = '$username'";
		$connect->query($attemptSql);

		// Log Login Attempt Reset(Username/AttemptCount)
		logThis('LOGIN_ATTEMPT_RESET',$username, $connect);
		// Log Login Success(Username)
		logThis('LOGIN_SUCCESS',$username, $connect);

		// Set Session expiration
		$tlSql = "SELECT timeLimit FROM settings";
		$tlRecord = $connect->query($tlSql);
		while($info = $tlRecord->fetch_assoc()){
			$minutes = $info['timeLimit'];
		}
		if($minutes == 0) {
			ini_set('session.gc_maxlifetime', 0);
		} else {
			ini_set('session.gc_maxlifetime', 60 * $minutes);
		}

		session_regenerate_id();

		// Stay signed via checkbox?
		if(isset($data->{'remember'})) {
			ini_set('session.gc_maxlifetime', 60*60*24*30); // Set to expire in 3 months & 10 days
			session_regenerate_id();

			// Log Login Attempt Reset(Username/AttemptCount)
			logThis('LOGIN_REMEMBER_ME',$username, $connect);
		}

		// Build JSON
		while($info = $testRecordSet->fetch_assoc()){
			$userId = $info['userId'];
			$username = $info['username'];
			$level = $info['level'];
			$verified = $info['verified'];
			$disabled = $info['disabled'];
		}

		$data = array(
			'userId' => $userId,
			'username' => $username,
			'level' => $level,
			'verified' => $verified,
			'disabled' => $disabled,
			'time' => $attemptDate
		);

		// Session Building
		$_SESSION['created'] = time();
		$_SESSION['disabled'] = $disabled;
		$_SESSION['username'] = $username;
	} else {
		if ($hulkSmash) {
			$error = 'HULK_SMASH';
		} else {
			$error = 'BAD_LOGIN';
		}
		$data = array(
			'result' => $error
		);
		die(json_encode($data));
	}
} else {
	$data = array(
		'result' => 'EMPTY_FIELD'
	);
	die(json_encode($data));
}

// return results
$connect->close();
echo json_encode($data);