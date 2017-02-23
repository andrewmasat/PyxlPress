<?php

include_once('../config/class.connect.php');
include_once('../general/class.email.php');

// Get username
if (isset($_SESSION['username'])) {

	$username = $connect->real_escape_string($_SESSION['username']);
	if (isset($_GET['request'])) {
		$request = $_GET['request'];
	} else if (isset($_GET['files'])) {
		$request = 'saveAvatar';
	} else {
		$data = json_decode(file_get_contents('php://input'));
		$request = $data->{'request'};
	}

	if ($request == 'getProfile') {
		$userSql = "SELECT * FROM users WHERE username = '$username'";
		$userRecordSet = $connect->query($userSql);

		while($info = $userRecordSet->fetch_assoc()){
			$avatar = $info['avatar'];
			$fullname = $info['displayName'];
			$username = $info['username'];
			$email = $info['email'];
			$sendEmail = $info['sendEmail'];
			$timezone = $info['timezone'];
			$verified = $info['verified'];
		}
		
		$data = array(
			'avatar' => $avatar,
			'fullname' => $fullname,
			'username' => $username,
			'email' => $email,
			'sendEmail' => $sendEmail,
			'timezone' => $timezone,
			'verified' => $verified
		);
		
		echo json_encode($data);
	} else if ($request == 'saveProfile') {
		// Handle Save Profile
		$userSql = "SELECT * FROM users WHERE username = '$username'";
		$userRecordSet = $connect->query($userSql);

		while($info = $userRecordSet->fetch_assoc()){
			$currentEmail = $info['email'];
		}

		$displayName = $connect->real_escape_string($data->{'displayname'});
		$email = $connect->real_escape_string($data->{'email'});

		if ($currentEmail != $email) {
			$insertSql = "UPDATE users SET displayName = '$displayName', email = '$email', verified = 0 WHERE username = '$username'";
		} else {
			$insertSql = "UPDATE users SET displayName = '$displayName', email = '$email' WHERE username = '$username'";
		}

		$connect->query($insertSql);

		$data = array(
			'saveProfile' => 'true'
		);
		echo json_encode($data);
	} else if ($request == 'saveAvatar') {
		if(isset($_GET['files'])) {	
			$avatar = '';

			$uploaddir = '../../pyxl-content/images/avatars/'.$username.'/';
			if (!is_dir($uploaddir)) {
				mkdir($uploaddir);
			}
			foreach($_FILES as $file) {
				if(move_uploaded_file($file['tmp_name'], $uploaddir .basename($file['name']))) {
					$avatar = $file['name'];
				}
			}

			$avatarSql = "UPDATE users SET avatar = '$avatar' WHERE username = '$username'";
			$connect->query($avatarSql);

			$data = array(
				'saveAvatar' => 'true'
			);
			echo json_encode($data);
		}
	} else if ($request == 'saveSettings') {
		$sendEmail = $connect->real_escape_string($data->{'sendEmail'});
		$timezone = $connect->real_escape_string($data->{'timezone'});

		$insertSql = "UPDATE users SET sendEmail = '$sendEmail', timezone = '$timezone' WHERE username = '$username'";
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
		
		$data = array(
			'resendActivation' => 'true'
		);
		echo json_encode($data);
	}
} else {
	$data = array(
		'loggedin' => 'false'
	);
	echo json_encode($data);
	die;
}