<?php

/*
 *
 * File Name: class.logger.php
 * Description: Logging
 *
 */

// General Logging function
function logThis($logType, $logBody, $connect) {

	$pageSql = "INSERT INTO logs (logType, logBody) VALUES ('$logType', '$logBody')";
	$connect->query($pageSql);

}

?>