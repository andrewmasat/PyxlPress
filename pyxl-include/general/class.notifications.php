<?php
include_once('../config/class.connect.php');

// Notifications in signed in
$notice = array();
$noticeCount = 0;

if (isset($_SESSION['username'])) {

	$username = $_SESSION['username'];
	if (isset($_GET['request'])) {
		$request = $_GET['request'];
	} else {
		$data = json_decode(file_get_contents('php://input'));
		$request = $data->{'request'};
	}

	if ($request == 'getNotice') {
		// SQL Request
		$noticeSql = "SELECT n.noticeTitle, n.noticeUrl, n.noticeIcon, n.viewed, n.timestamp
									FROM notifications n INNER JOIN users u ON u.userId = n.userId
									WHERE u.username = '$username'
									ORDER BY timestamp DESC
									LIMIT 5";

		$envProp = $connect->query($noticeSql);
		while($info = $envProp->fetch_assoc()){

			// Set Dates
			$date = new DateTime(date('Y-m-d'));
			$match_date = new DateTime(date('Y-m-d', strtotime($info['timestamp'])));
			$interval = $date->diff($match_date);

			if ($interval->days == 0) {
				$timestamp = date('g:i a', strtotime($info['timestamp']));
			} else if ($interval->days == 1) {
				if($interval->invert == 1) {
					$timestamp = 'Yesterday';
				}
			} else if ($interval->days < 7) {
				$timestamp = $interval->days . ' days ago';
			} else {
				$timestamp = 'a while ago';
			}

			// Set Notice Count
			if ($info['viewed'] == 0) {
				$noticeCount++;
			}

			// Build Notice Log
			$notice[] = array(
				'noticeTitle' => $info['noticeTitle'],
				'noticeUrl' => $info['noticeUrl'],
				'noticeIcon' => $info['noticeIcon'],
				'viewed' => $info['viewed'],
				'timestamp' => $timestamp
			);
		}

		$data = array (
			'notice' => $notice,
			'noticeCount' => $noticeCount
		);
	} else if ($request == 'clearAlerts') {
		$clearSql = "UPDATE notifications n
								INNER JOIN users u ON u.userId = n.userId
								SET n.viewed = 1
								WHERE u.username = '$username' AND n.viewed = 0";

		$connect->query($clearSql);

		$data = array(
			'clearAlerts' => 'success'
		);
	}
} else {
	$data = array(
		'access' => 'false'
	);
}

// return results
$connect->close();
echo json_encode($data);