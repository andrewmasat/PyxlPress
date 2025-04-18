<?php

function pb_permalink($string) {
	//Lower case everything
	$string = strtolower($string);
	//Make alphanumeric (removes all other characters)
	$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
	//Clean up multiple dashes or whitespaces
	$string = preg_replace("/[\s-]+/", " ", $string);
	//Convert whitespaces and underscore to dash
	$string = preg_replace("/[\s_]/", "-", $string);
	return $string;
}

function pb_post_list($pluginName, $hookData, $secure, $connect) {
	$query = "SELECT * FROM pb_posts ORDER BY pb_id DESC";
	$posts = $connect->query($query);

	$postList = array();
	while($info = $posts->fetch_assoc()){
		$postList[] = array(
			'pb_id' => $info['pb_id'],
			'pb_author' => $info['pb_author'],
			'pb_title' => $info['pb_title'],
			'pb_content' => $info['pb_content'],
			'pb_permalink' => $info['pb_permalink'],
			'pb_status' => $info['pb_status'],
			'pb_remove' => $info['pb_remove'],
			'pb_update' => siteDateTime($info['pb_update'], $connect),
			'pb_timestamp' => siteDateTime($info['pb_timestamp'], $connect)
		);
	}

	$data = array(
		'pluginName' => $pluginName,
		'postList' => $postList
	);
	echo json_encode($data);
}

function pb_post_single($pluginName, $hookData, $secure, $connect) {
	$query = "SELECT * FROM pb_posts WHERE pb_permalink = '$hookData'";
	$posts = $connect->query($query);

	while($info = $posts->fetch_assoc()){
		$postSingle = array(
			'pb_id' => $info['pb_id'],
			'pb_author' => $info['pb_author'],
			'pb_title' => $info['pb_title'],
			'pb_content' => $info['pb_content'],
			'pb_permalink' => $info['pb_permalink'],
			'pb_status' => $info['pb_status'],
			'pb_remove' => $info['pb_remove'],
			'pb_update' => siteDateTime($info['pb_update'], $connect),
			'pb_timestamp' => siteDateTime($info['pb_timestamp'], $connect)
		);
	}

	$data = array(
		'pluginName' => $pluginName,
		'postSingle' => $postSingle
	);
	echo json_encode($data);
}

function pb_post_save($pluginName, $hookData, $secure, $connect) {
	if ($secure) {
		$hookDataArr = new ArrayObject($hookData);
		$hookData = $hookDataArr->getArrayCopy();

		$author = $_SESSION['username'];
		$title = $connect->real_escape_string($hookData['title']);
		$content = $connect->real_escape_string($hookData['content']);
		$siteUrl = $connect->real_escape_string($hookData['siteUrl']);

		$permalink = $siteUrl . '/blog/' . pb_permalink($title);

		$query = "INSERT INTO pb_posts (pb_author, pb_title, pb_content, pb_permalink, pb_status, pb_remove)
							VALUES ('$author','$title', '$content', '$permalink', 0, 0)";
		$connect->query($query);
		$id = $connect->insert_id;

		$data = array(
			'pluginName' => $pluginName,
			'hookData' => $hookData,
			'id' => $id
		);
	} else {
		$data = array(
			'secure' => $secure
		);
	}
	echo json_encode($data);
}

function pb_post_edit_get($pluginName, $hookData, $secure, $connect) {
	if ($secure) {
		$query = "SELECT * FROM pb_posts WHERE pb_id = $hookData";
		$post = $connect->query($query);

		while($info = $post->fetch_assoc()){
			$pb_content = $info['pb_content'];
			$pb_id = $info['pb_id'];
			$pb_permalink = $info['pb_permalink'];
			$pb_status = $info['pb_status'];
			$pb_timestamp = $info['pb_timestamp'];
			$pb_title = $info['pb_title'];
			$pb_update = $info['pb_update'];
		}

		$data = array(
			'pluginName' => $pluginName,
			'pb_content' => $pb_content,
			'pb_id' => $pb_id,
			'pb_permalink' => $pb_permalink,
			'pb_status' => $pb_status,
			'pb_timestamp' => siteDateTime($pb_timestamp, $connect),
			'pb_title' => $pb_title,
			'pb_update' => siteDateTime($pb_update, $connect)
		);
	} else {
		$data = array(
			'secure' => $secure
		);
	}
	echo json_encode($data);
}

function pb_post_edit_save($pluginName, $hookData, $secure, $connect) {
	if ($secure) {
		$hookDataArr = new ArrayObject($hookData);
		$hookData = $hookDataArr->getArrayCopy();

		$author = $_SESSION['username'];
		$id = $connect->real_escape_string($hookData['pb_id']);
		$content = $connect->real_escape_string($hookData['pb_content']);
		$siteUrl = $connect->real_escape_string($hookData['pb_siteUrl']);
		$status = $connect->real_escape_string($hookData['pb_status']);
		$title = $connect->real_escape_string($hookData['pb_title']);

		$permalink = $siteUrl . '/blog/' . pb_permalink($title);
		$hookData['pb_permalink'] = $permalink;

		$query = "UPDATE pb_posts SET pb_title = '$title', pb_content = '$content', pb_permalink = '$permalink', pb_status = '$status' WHERE pb_id = $id";
		$connect->query($query);

		$getUpdate = "SELECT * FROM pb_posts WHERE pb_id = $id";
		$getUpdatePost = $connect->query($getUpdate);

		while($info = $getUpdatePost->fetch_assoc()){
			$hookData['pb_timestamp'] = siteDateTime($info['pb_timestamp'], $connect);
			$hookData['pb_update'] = siteDateTime($info['pb_update'], $connect);
		}

		$data = array(
			'pluginName' => $pluginName,
			'hookData' => $hookData
		);
	} else {
		$data = array(
			'secure' => $secure
		);
	}
	echo json_encode($data);
}

function pb_post_delete($pluginName, $hookData, $secure, $connect) {
	if ($secure) {
		$query = "DELETE FROM pb_posts WHERE pb_id = '$hookData'";
		$connect->query($query);

		$data = array(
			'pluginName' => $pluginName,
			'hookData' => $hookData
		);
	} else {
		$data = array(
			'secure' => $secure
		);
	}
	echo json_encode($data);
}

function get_hook_blog($options, $connect) {
	$data = array(
		'options' => $options
	);
	echo json_encode($data);
}

?>