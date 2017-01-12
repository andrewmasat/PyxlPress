<?php

/*
 *
 * File Name: class.protect.php
 * Description: Protect administrative functionality
 *
 * @param string $username
 * @return boolean true
 *
 */

function protect($username, $connect) {
	
	// Need to check if the username has the appropriate
	// user level to gain access to the requesting page.
	$joinSql = "SELECT level FROM users WHERE username = '$username'";
	$protectResults = $connect->query($joinSql);

	while($info = $protectResults->fetch_assoc()){
		$level = $info['level'];
	}

	if ($level >= 3) {
		return true;
	} else {
		return false;
	}

}