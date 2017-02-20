<?php

/*
 *
 * File Name: class.admin.php
 * Description: Administrative functionality
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('../general/class.email.php');
include_once('../general/class.logger.php');
include_once('class.protect.php');

// Get username
if (protect($_SESSION['username'], $connect)) {

	$username = $connect->real_escape_string($_SESSION['username']);
	if (isset($_GET['request'])) {
		$request = $_GET['request'];
	} else {
		$data = json_decode(file_get_contents('php://input'));
		$request = $data->{'request'};
	}

	if ($request == 'getHealth') {
		// Get Current Version
		$versionSql = "SELECT version FROM settings";
		$versionResult = $connect->query($versionSql);

		while($info = $versionResult->fetch_assoc()){
			$currentVersion = $info['version'];
		}
		// Get Latest Version
		$versionUrl = "http://andrewmasat.com/research/versions/version.txt";
		$changeLog = "http://andrewmasat.com/research/versions/changelog.txt";
		$versionString = file_get_contents($versionUrl);
		$changeLogString = file_get_contents($changeLog);
		$versions = explode(",",$versionString);
		$latestVersion = $versions[0];
		$needUpdate = false;
		$isImportant = false;
		$currentVersion = explode('.', $currentVersion);
		$latestVersion = explode('.', $latestVersion);

		// Check for update and then determine how important it is.
		if ($latestVersion > $currentVersion) {

			// Check major version
			if ($latestVersion[0] > $currentVersion[0]) {
				$needUpdate = true;
				$isImportant = true;
			} else if ($latestVersion[1] > $currentVersion[1]) {
				$needUpdate = true;
				$isImportant = false;
			}
		}

		$userCountSql = "SELECT * FROM users";
		$userCountRecordSet = $connect->query($userCountSql);
		$userCount = $userCountRecordSet->num_rows;
		
		$data = array(
			'userCount' => $userCount,
			'needUpdate' => $needUpdate,
			'isImportant' => $isImportant,
			'currentVersion' => $currentVersion[0].'.'.$currentVersion[1],
			'latestVersion' => $latestVersion[0].'.'.$latestVersion[1],
			'changeLog' => $changeLogString
		);
		
		echo json_encode($data);
	} else if ($request == 'getSettings') {
		$settingsSql = "SELECT * FROM settings";
 
		$envProp = $connect->query($settingsSql);
		while($info = $envProp->fetch_assoc()){
			$allowRegister = $info['allowRegister'];
			$debug = $info['debug'];
			$fixedWidth = $info['fixedWidth'];
			$logLogins = $info['logLogins'];
			$siteName = $info['siteName'];
			$siteTimezone = $info['siteTimezone'];
			$siteUrl = $info['siteUrl'];
			$timeLimit = $info['timeLimit'];
			$version = $info['version'];
		}

		$data = array (
			'allowRegister' => $allowRegister,
			'debug' => $debug,
			'fixedWidth' => $fixedWidth,
			'logLogins' => $logLogins,
			'siteName' => $siteName,
			'siteTimezone' => $siteTimezone,
			'siteUrl' => $siteUrl,
			'timeLimit' => $timeLimit,
			'version' => $version
		);
		
		echo json_encode($data);
	} else if ($request == 'saveSettings') {
		$allowRegister = $connect->real_escape_string($data->{'allowRegister'});
		$debug = $connect->real_escape_string($data->{'debug'});
		$fixedWidth = $connect->real_escape_string($data->{'fixedWidth'});
		$logLogins = $connect->real_escape_string($data->{'logLogins'});
		$siteName = $connect->real_escape_string($data->{'siteName'});
		$siteTimezone = $connect->real_escape_string($data->{'siteTimezone'});
		$siteUrl = $connect->real_escape_string($data->{'siteUrl'});
		$timeLimit = $connect->real_escape_string($data->{'timeLimit'});

		$insertSql = "UPDATE settings SET allowRegister = '$allowRegister', debug = '$debug', fixedWidth = '$fixedWidth', logLogins = '$logLogins', siteName = '$siteName', siteTimezone = '$siteTimezone', siteUrl = '$siteUrl', timeLimit = '$timeLimit'";
		$connect->query($insertSql);

		// Log Settings Update
		logThis('UPDATE_SETTINGS',$username.'/'.$allowRegister.'/'.$debug.'/'.$fixedWidth.'/'.$logLogins.'/'.$siteName.'/'.$siteTimezone.'/'.$siteUrl.'/'.$timeLimit, $connect);

		$data = array(
			'saveSettings' => 'true'
		);
		echo json_encode($data);
	} else if ($request == 'getUserList') {
		$userSql = "SELECT * FROM users u INNER JOIN roles r ON u.level = r.level ORDER BY userId DESC";
		$userRecordSet = $connect->query($userSql);

		$data = array();
		while($info = $userRecordSet->fetch_assoc()){
			$data[] = array(
				'createDate' => date('m/d/y g:i a', strtotime($info['createDate'])),
				'disabled' => $info['disabled'],
				'displayName' => $info['displayName'],
				'email' => $info['email'],
				'level' => $info['level'],
				'role' => $info['roleName'],
				'userId' => $info['userId'],
				'username' => $info['username'],
				'verified' => $info['verified']
			);
		}

		echo json_encode($data);
	} else if ($request == 'getUser') {
		$userId = $_GET['id'];
		$userSql = "SELECT * FROM users u INNER JOIN roles r ON u.level = r.level WHERE userId = $userId";
		$userRecordSet = $connect->query($userSql);

		while($info = $userRecordSet->fetch_assoc()){
			$avatar = $info['avatar'];
			$createDate = date('m/d/y g:i a', strtotime($info['createDate']));
			$disabled = $info['disabled'];
			$displayName = $info['displayName'];
			$email = $info['email'];
			$level = $info['level'];
			$loginAttempt = date('m/d/y g:i a', strtotime($info['loginAttemptDate']));
			$role = $info['roleName'];
			$userId = $info['userId'];
			$username = $info['username'];
			$verified = $info['verified'];
		}

		$roleSql = "SELECT * FROM roles";
		$roleRecordSet = $connect->query($roleSql);

		$roles = array();
		while($info = $roleRecordSet->fetch_assoc()){
			$roles[] = array(
				'roleId' => $info['roleId'],
				'roleName' => $info['roleName'],
				'roleLevel' => $info['level']
			);
		}

		$data = array(
			'avatar' => $avatar,
			'createDate' => $createDate,
			'disabled' => $disabled,
			'displayName' => $displayName,
			'email' => $email,
			'level' => $level,
			'loginAttempt' => $loginAttempt,
			'role' => $role,
			'userId' => $userId,
			'username' => $username,
			'verified' => $verified,
			'roles' => $roles
		);

		echo json_encode($data);
	} else if ($request == 'saveUser') {
		$userId = $data->{'id'};
		$email = $connect->real_escape_string($data->{'email'});
		$displayName = $connect->real_escape_string($data->{'displayName'});
		$clearAvatar = $connect->real_escape_string($data->{'clearAvatar'});
		$level = $connect->real_escape_string($data->{'role'});
		$disableAccount = $connect->real_escape_string($data->{'disableAccount'});

		// SAFE GUARD //
		// User Id = 1 will not be allowed to be edited in the admin panel
		// This will prevent changing role level or disabling account
		if ($userId != 1) {
			if ($clearAvatar == 1) {
				$userSql = "UPDATE users SET email = '$email', displayName = '$displayName', level = $level, disabled = $disableAccount, avatar = '' WHERE userId = $userId";
			} else {
				$userSql = "UPDATE users SET email = '$email', displayName = '$displayName', level = $level, disabled = $disableAccount WHERE userId = $userId";
			}
			$connect->query($userSql);

			// Log Settings Update
			logThis('UPDATE_USERS',$username.'/'.$userId.'/'.$email.'/'.$displayName.'/'.$level.'/'.$disableAccount, $connect);
			
			$data = array(
				'saveSettings' => 'true'
			);
		} else {
			$data = array(
				'saveSettings' => 'false'
			);
		}

		echo json_encode($data);
	} else if ($request == 'getRoles') {
		$roleSql = "SELECT r.roleId, r.roleName, r.level, Count(u.level) as count FROM roles r LEFT JOIN users u ON u.level = r.level GROUP BY r.roleName ORDER BY r.roleId ASC";
		$roleRecordSet = $connect->query($roleSql);

		$data = array();
		while($info = $roleRecordSet->fetch_assoc()){
			$data[] = array(
				'roleId' => $info['roleId'],
				'roleName' => $info['roleName'],
				'roleLevel' => $info['level'],
				'userCount' => $info['count']
			);
		}

		echo json_encode($data);
	} else if ($request == 'saveRoles') {
		$roleName1 = $connect->real_escape_string($data->{'roleName1'});
		$roleName2 = $connect->real_escape_string($data->{'roleName2'});
		$roleName3 = $connect->real_escape_string($data->{'roleName3'});
		$roleName4 = $connect->real_escape_string($data->{'roleName4'});

		$insertSql = "UPDATE roles SET roleName = '$roleName1' WHERE roleId = 1";
		$connect->query($insertSql);
		$insertSql = "UPDATE roles SET roleName = '$roleName2' WHERE roleId = 2";
		$connect->query($insertSql);
		$insertSql = "UPDATE roles SET roleName = '$roleName3' WHERE roleId = 3";
		$connect->query($insertSql);
		$insertSql = "UPDATE roles SET roleName = '$roleName4' WHERE roleId = 4";
		$connect->query($insertSql);

		$data = array(
			'saveSettings' => 'true'
		);
		echo json_encode($data);
	} else if ($request == 'savePassword') {
		$oldpassword = $connect->real_escape_string($data->{'currentPassword'});
		$newpassword = $connect->real_escape_string($data->{'newPassword'});
		$newpassword2 = $connect->real_escape_string($data->{'confirmPassword'});

		if (!empty($username) && !empty($oldpassword) && !empty($newpassword) && !empty($newpassword2)) {
			if ($newpassword == $newpassword2) {
				$saltSql = "SELECT salt FROM users WHERE username = '$username'";
				$saltRecordSet = $connect->query($saltSql);

				while($info = $saltRecordSet->fetch_assoc()){
					$salt = $info['salt'];
				}

				$saltedPW =  $oldpassword . $salt;
				$hashedPW = hash('sha256', $saltedPW);

				$testSql = "SELECT * FROM users WHERE username = '$username' AND password = '$hashedPW'";
				$testRecordSet = $connect->query($testSql);
				$testResult = $testRecordSet->num_rows;

				if ($testResult > 0) {
					$newSalt = md5(uniqid(mt_rand(),true));
					$passwordSalt = $newpassword . $newSalt;
					$passwordHash = hash("sha256", $passwordSalt);
			
					$insertSql = "UPDATE users SET password = '$passwordHash', salt = '$newSalt' WHERE username = '$username'";
					$connect->query($insertSql);

					// Log Settings Update
					logThis('UPDATE_PASSWORD',$username, $connect);
					
					$data = array(
						'save' => 'success'
					);
					echo json_encode($data);
					
				} else {
					die('BAD_LOGIN');
				}
			} else {
				die('MISMATCH');
			}
		} else {
			die('EMPTY_FIELD');
		}
	} else if ($request == 'resendActivation') {
		$userSql = "SELECT * FROM users WHERE username = '$username'";
		$userRecordSet = $connect->query($userSql);
		while($info = $userRecordSet->fetch_assoc()){
			$salt = $info['salt'];
			$email = $info['email'];
		};

		$activatePreCode = $email . $salt;
		$activationCode = hash("sha256", $activatePreCode);

		sendEmail('register', $email, $activationCode);

		// Log Settings Update
		logThis('EMAIL_REGISTER_RESEND',$username, $connect);
		
		$data = array(
			'resendActivation' => 'true'
		);
		echo json_encode($data);
	}
} else {
	$data = array(
		'access' => 'false'
	);
	echo json_encode($data);
	die;
}