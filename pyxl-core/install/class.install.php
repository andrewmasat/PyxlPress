<?php
// Initial Settings
$version = '0.7';

// Install PyxlPress
$settings = array();

if ($_POST['request'] == 'install') {
	foreach ($_POST as $key => $value) {
		$settings[$key] = $value;
	}

	$connect = @new mysqli($settings['dbhost'], $settings['username'], $settings['password'], $settings['dbname']);

	if ($connect->connect_errno) {
		$data = array (
			'connect' => 'failed',
			'result' => $connect->connect_error
		);
	} else {
		// $testSql = "SELECT * FROM settings";
		// $testRecordSet = $connect->query($testSql);
		// $existResults = $testRecordSet->num_rows;

		// if ($existResults == 0) {
			// Build Database
			$connect->query("CREATE TABLE IF NOT EXISTS logs (
												logId int(11) NOT NULL AUTO_INCREMENT,
												logType varchar(255) NOT NULL,
												logBody varchar(1000) DEFAULT NULL,
												timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
												PRIMARY KEY (logId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS roles (
												roleId int(11) NOT NULL AUTO_INCREMENT,
												roleName varchar(255) NOT NULL,
												level int(11) NOT NULL,
												PRIMARY KEY (roleId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS security (
												securityId int(11) NOT NULL AUTO_INCREMENT,
												location varchar(255) NOT NULL,
												level int(11) NOT NULL,
												active int(1) NOT NULL,
												PRIMARY KEY (securityId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS settings (
												siteName varchar(255) NOT NULL,
												siteUrl varchar(255) NOT NULL,
												siteEmail varchar(255) NOT NULL,
												siteTimeFormat varchar(255) NOT NULL,
												siteTimezone varchar(255) NOT NULL,
												version varchar(255) NOT NULL,
												theme varchar(255) NOT NULL,
												allowRegister int(1) NOT NULL,
												timeLimit int(1) NOT NULL,
												logLogins int(1) NOT NULL,
												fixedWidth int(1) NOT NULL,
												debug int(1) NOT NULL,
												KEY allowRegister (allowRegister)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS plugins (
												pluginId int(11) NOT NULL AUTO_INCREMENT,
												pluginName varchar(255) NOT NULL,
												pluginActive int(1) NOT NULL,
												pluginVersion varchar(255) NOT NULL,
												PRIMARY KEY (pluginId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS pages (
												pageId int(11) NOT NULL AUTO_INCREMENT,
												pageFileName varchar(255) NOT NULL,
												pagePermalink varchar(255) NOT NULL,
												pageTheme varchar(255) NOT NULL,
												PRIMARY KEY (pageId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS users (
												userId int(11) NOT NULL AUTO_INCREMENT,
												username varchar(50) NOT NULL,
												password char(128) NOT NULL,
												salt char(128) NOT NULL,
												level int(11) NOT NULL,
												verified int(1) NOT NULL,
												email varchar(190) NOT NULL,
												sendEmail int(1) NOT NULL,
												timezone varchar(255) NOT NULL,
												displayName varchar(50) DEFAULT NULL,
												avatar varchar(255) DEFAULT NULL,
												disabled int(11) NOT NULL,
												loginAttempt int(11) NOT NULL,
												loginAttemptDate timestamp NULL DEFAULT NULL,
												createDate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
												PRIMARY KEY (userId),
												UNIQUE KEY username (username),
												UNIQUE KEY email (email)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS media (
												mediaId int(11) NOT NULL AUTO_INCREMENT,
												mediaTitle varchar(150) NOT NULL,
												mediaPermalink varchar(1000) NOT NULL,
												mediaType varchar(100) NOT NULL,
												mediaExtension varchar(50) NOT NULL,
												mediaSize int(255) NOT NULL,
												PRIMARY KEY (mediaId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS mediaMeta (
												mediaMetaId int(11) NOT NULL AUTO_INCREMENT,
												mediaId int(11) NOT NULL,
												ImageWidth int(10) NULL,
												ImageLength int(10) NULL,
												Make varchar(50) NULL,
												Model varchar(50) NULL,
												Orientation int(1) NULL,
												XResolution varchar(50) NULL,
												YResolution varchar(50) NULL,
												DateTime DATETIME NULL,
												PRIMARY KEY (mediaMetaId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("CREATE TABLE IF NOT EXISTS notifications (
												notificationId int(11) NOT NULL AUTO_INCREMENT,
												userId int(11) NOT NULL,
												noticeTitle varchar(255) NOT NULL,
												noticeUrl varchar(1000) NOT NULL,
												noticeIcon varchar(255) NOT NULL,
												viewed int(1) NOT NULL,
												timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
												PRIMARY KEY (notificationId)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

			$connect->query("INSERT INTO roles (roleId, roleName, level) VALUES
											(1, 'Guest', 0),
											(2, 'User', 1),
											(3, 'Moderator', 2),
											(4, 'Admin', 3);");

			$connect->query("INSERT INTO security (securityId, location, level, active) VALUES
											(1, 'activate', 0, 1),
											(2, 'admin', 3, 1),
											(3, 'home', 0, 1),
											(4, 'login', 0, 1),
											(5, 'media', 3, 1),
											(6, 'pages', 3, 1),
											(7, 'plugins', 3, 1),
											(8, 'profile', 1, 1),
											(9, 'register', 0, 1),
											(10, 'themes', 3, 1),
											(11, 'welcome', 1, 1);");

			$connect->query("INSERT INTO pages (pageId, pagePermalink, pageFileName, pageTheme) VALUES
											(1, 'index', 'index', 'pyxlate'),
											(2, 'pricing', 'pricing', 'pyxlate'),
											(3, 'about', 'about', 'pyxlate'),
											(4, 'support', 'support', 'pyxlate'),
											(5, 'blog', 'blog', 'pyxlate');");

			$connect->query("INSERT INTO notifications (notificationId, userId, noticeTitle, noticeUrl, noticeIcon, viewed, timestamp) VALUES
											('1', '1', 'Welcome to PyxlPress!', 'welcome', 'fa-exclamation-triangle', '0', CURRENT_TIMESTAMP);");

			// Write config.php
			$fp = fopen(realpath(__DIR__ . "/../../pyxl-include") . "/config.php", "w");
fwrite($fp, '<?php

	///////////////////////////////////////////////////////
	//  Important ! These must be filled in correctly.   //
	// Database details are required to use this script. //
	///////////////////////////////////////////////////////

	$host = "'.$settings['dbhost'].'"; // If you don\'t know what your host is, it\'s safe to leave it localhost
	$dbName = "'.$settings['dbname'].'"; // Database name
	$dbUser = "'.$settings['username'].'"; // username
	$dbPass = "'.$settings['password'].'"; // Password

	// Session Info
	$attemptLimit = "5"; // Default: 5 Attempts
	$attemptLimitLockout = "300"; // Default: 5 Minutes

?>');

			fclose($fp);

			$data = array (
				'connect' => 'success'
			);
		// } else {
		// 	$data = array (
		// 		'connect' => 'failed',
		// 		'result' => 'Settings already installed'
		// 	);
		// }
		$connect->close();
	}
} else if ($_POST['request'] == 'settings') {
	include_once(realpath(__DIR__ . "/../../pyxl-include/config.php"));
	$connect = new mysqli($host, $dbUser, $dbPass, $dbName);

	$siteName = $_POST['siteName'];
	$siteEmail = $_POST['siteEmail'];
	$siteUrl = $_POST['siteUrl'];
	$siteTimezone = $_POST['siteTimezone'];

	$insertSql = "INSERT INTO settings (siteName, siteUrl, siteEmail, siteTimeFormat, siteTimezone, version, theme, allowRegister, timeLimit, logLogins, fixedWidth, debug)
								VALUES ('$siteName', '$siteUrl', '$siteEmail', 'm/d/y g:i a', '$siteTimezone', '$version', 'pyxlate', 1, 30, 0, 0, 0);";
	$connect->query($insertSql);

	$data = array(
		'saveSettings' => 'true'
	);
} else if ($_POST['request'] == 'account') {
	include_once(realpath(__DIR__ . "/../../pyxl-include/config.php"));
	$connect = new mysqli($host, $dbUser, $dbPass, $dbName);

	$username = $_POST['username'];
	$password = $_POST['password'];
	$email = $_POST['email'];

	$salt = md5(uniqid(mt_rand(),true));
	$passwordSalt = $password . $salt;
	$passwordHash = hash("sha256", $passwordSalt);

	$newUserSql = "INSERT INTO users (username,password,salt,level,verified,email,sendEmail,timezone,displayName,loginAttemptDate,disabled) 
									VALUES ('$username','$passwordHash','$salt',3,1,'$email',1,'America/Chicago','$username',NULL,0)";
	$connect->query($newUserSql);

	$data = array (
		'saveAccount' => 'true'
	);
} else {
	$data = array (
		'connect' => 'failed',
		'result' => 'No post information'
	);
}

echo json_encode($data);

?>