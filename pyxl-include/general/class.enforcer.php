<?php

/*
 *
 * File Name: class.enforcer.php
 * Description: Enforcer checks the user navigating the site and maintains their
 * location and level status to confirm they are allowed on the requested pages.
 *
 */

// Includes
include_once('../config/class.connect.php');
	
// Location access
if (isset($_GET['location'])) {
	$location = $_GET['location'];
} else {
	$location = 'home';
}
// Get Location Security Level
$secureSql = "SELECT * FROM security WHERE location = '$location'";
$secureRecordSet = $connect->query($secureSql);
while($info = $secureRecordSet->fetch_assoc()){
	$locationActive = $info['active'];
	$locationLevel = $info['level'];
}

if (!isset($locationLevel)) {
	$locationActive = 0;
	$locationLevel = 0;
}

// Who are you?
if (isset($_SESSION['username'])) {
	// Set Parameters
	$username = $connect->real_escape_string($_SESSION['username']);

	// Get User Information
	$userSql = "SELECT * FROM users u INNER JOIN roles r ON u.level = r.level WHERE username = '$username'";
	$userRecordSet = $connect->query($userSql);
	while($info = $userRecordSet->fetch_assoc()){
		$avatar = $info['avatar'];
		$userId = $info['userId'];
		$username = $info['username'];
		$fullname = $info['displayName'];
		$level = $info['level'];
		$role = $info['roleName'];
		$disabled = $info['disabled'];
		$verified = $info['verified'];
	}

	// Location not found? Lock it down!
	if ($locationLevel == null) {
		$locationLevel = 999;
	}

	// Is User Active AND have security for the location?
	if ($disabled == 0 && $level >= $locationLevel && $locationActive) {
		$data = array(
			'avatar' => $avatar,
			'userId' => $userId,
			'username' => $username,
			'fullname' => $fullname,
			'level' => $level,
			'role' => $role,
			'location' => $location,
			'verified' => $verified,
			'locationlvl' => $locationLevel
		);
	} else if ($level < $locationLevel) {
		$data = array(
			'access' => 'false',
			'location' => $location,
			'locationlvl' => $level,
			'result' => 'RESTRICTED'
		);
	} else {
		$data = array(
			'access' => 'false',
			'result' => 'DISABLED'
		);
	}

} else {
	if ($locationLevel == 0) {
		$data = array(
			'access' => 'true',
			'result' => 'GUEST'
		);
	} else {
		$data = array(
			'access' => 'false',
			'result' => 'NO_LOGIN'
		);
	}
}

// return results
$connect->close();
echo json_encode($data);