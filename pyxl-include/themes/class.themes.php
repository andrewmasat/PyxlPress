<?php

/*
 *
 * File Name: class.themes.php
 * Description: Theme management functionality
 *
 */

// Includes
include_once('../config/class.connect.php');
include_once('class.themeFiles.php');

if (isset($_GET['request'])) {
	if (empty($_GET['request'])) {
		$request = 'index';
	} else {
		$request = $_GET['request'];
	}
} else {
	$data = json_decode(file_get_contents('php://input'));
	$request = $data->{'request'};
}

if ($request == 'getThemes') {
	$activeThemeSql = "SELECT * FROM settings";

	$activeTheme = $connect->query($activeThemeSql);
	while($info = $activeTheme->fetch_assoc()){
		$theme = $info['theme'];
	}

	$data = array();
	if ($handle = opendir(realpath(__DIR__ ."/../../pyxl-content/themes/"))) {
		$blacklist = array('.', '..');
		while (false !== ($folder = readdir($handle))) {
			if (!in_array($folder, $blacklist)) {
				// Is Active?
				if ($theme == $folder) {
					$active = true;
				} else {
					$active = false;
				}

				// Get Theme Info
				$styleUrl = realpath(__DIR__ ."/../../pyxl-content/themes/".$folder."/css/style.css");
				if (file_exists($styleUrl)) {
					$themeInfo = file_get_contents($styleUrl);
				} else {
					$themeInfo = false;
				}
				
				if ($themeInfo) {
					$themeNameEx = "/Theme:.*/";
					$themeAuthorEx = "/Author:.*/";
					$themeDescEx = "/Description:.*/";
					if(preg_match_all($themeNameEx, $themeInfo, $matches)){
						$themeName = str_replace("Theme: ","",$matches[0][0]);
					}
					if(preg_match_all($themeAuthorEx, $themeInfo, $matches)){
						$themeAuthor = str_replace("Author: ","",$matches[0][0]);
					}
					if(preg_match_all($themeDescEx, $themeInfo, $matches)){
						$themeDesc = str_replace("Description: ","",$matches[0][0]);
					}
				} else {
					$themeName = $folder;
					$themeAuthor = 'Unknown';
					$themeDesc = '';
				}

				$data[] = array (
					'active' => $active,
					'folder' => $folder,
					'themeName' => $themeName,
					'themeAuthor' => $themeAuthor,
					'themeDesc' => $themeDesc
				);
			}
		}
		closedir($handle);
	}
	echo json_encode($data);
}

if ($request == 'activateTheme') {
	$themeName = $data->{'theme'};

	$themeSql = "UPDATE settings SET theme = '$themeName'";
	$connect->query($themeSql);

	if (isset($_SESSION['previewTheme'])) {
		unset($_SESSION['previewTheme']);
	}

	$data = array(
		'saveSettings' => 'true',
		'theme' => $themeName
	);
	echo json_encode($data);
}

if ($request == 'previewTheme') {
	$themeName = $data->{'theme'};

	$_SESSION['previewTheme'] = $themeName;

	$data = array(
		'saveSettings' => 'true',
		'previewTheme' => $themeName
	);
	echo json_encode($data);
}

if ($request == 'getThemeFiles') {
	$theme = $_GET['theme'];
	$data = array();
	// Get HTML
	if ($handle = opendir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/templates"))) {
		$blacklist = array('.', '..');
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $blacklist)) {
				$fileName = str_replace('.html','',$file);
				$data[] = array (
					'fileName' => $fileName,
					'file' => $file,
					'type' => 'html'
				);
			}
		}
		closedir($handle);
	}

	// Get CSS
	if ($handle = opendir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/css"))) {
		$blacklist = array('.', '..');
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $blacklist)) {
				$fileName = str_replace('.css','',$file);
				$data[] = array (
					'fileName' => $fileName,
					'file' => $file,
					'type' => 'css'
				);
			}
		}
		closedir($handle);
	}

	// Get Javascript
	if ($handle = opendir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/views"))) {
		$blacklist = array('.', '..');
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $blacklist)) {
				$fileName = str_replace('.js','',$file);
				$data[] = array (
					'fileName' => $fileName,
					'file' => $file,
					'type' => 'js'
				);
			}
		}
		closedir($handle);
	}

	echo json_encode($data);
}

if ($request == 'getFile') {
	$theme = $_GET['theme'];
	$editDir = $_GET['dir'];
	$editFile = $_GET['file'];
	$fileName = str_replace('.html','',$editFile);

	if ($editDir) {
		$fileDirect = realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/".$editDir."/".$editFile);
		$fileContent = file_get_contents($fileDirect);

		$pagesSql = "SELECT * FROM pages WHERE pageFileName = '$fileName' AND pageTheme = '$theme'";
		$pageRecords = $connect->query($pagesSql);

		// SQL Request
		$page = array();
		while($info = $pageRecords->fetch_assoc()){
			$page = array (
				'pageId' => $info['pageId'],
				'pagePermalink' => $info['pagePermalink'],
				'pageFileName' => $info['pageFileName']
			);
		}

		$data = array (
			'fileContent' => $fileContent,
			'fileDate'=> date("F/d/Y h:i:s A", filemtime($fileDirect)),
			'fileName' => $fileName,
			'fileSize' => filesize($fileDirect),
			'page' => $page
		);
	} else {
		$data = array (
			'fileContent' => false
		);
	}

	echo json_encode($data);
}

if ($request == 'editFile') {
	$file = realpath(__DIR__ ."/../../pyxl-content/themes/".$data->{'theme'}."/".$data->{'dir'}."/".$data->{'file'});
	$fileContent = $data->{'fileContent'};
	$fileName = $data->{'fileName'};
	$theme = $data->{'theme'};
	if (isset($data->{'pagePermalink'})) {
		$pagePermalink = $data->{'pagePermalink'};
	}

	file_put_contents($file, $fileContent);

	$updatePermalink = false;
	if ($data->{'dir'} === 'templates' && isset($pagePermalink)) {
		$fileName = str_replace('.html','',$data->{'file'});
		$pageSql = "UPDATE pages SET pagePermalink = '$pagePermalink' WHERE pageFileName = '$fileName' AND pageTheme = '$theme'";
		$connect->query($pageSql);

		$updatePermalink = true;
	}

	$data = array(
		'saveFile' => 'success',
		'updatePermalink' => $updatePermalink
	);
	echo json_encode($data);
}

if ($request == 'saveFile') {
	$file = realpath(__DIR__ ."/../../pyxl-content/themes/".$data->{'theme'}."/".$data->{'dir'})."/".$data->{'file'};
	$fileContent = $data->{'fileContent'};
	$fileName = $data->{'fileName'};
	$theme = $data->{'theme'};

	file_put_contents($file, $fileContent);

	$insertPermalink = false;
	if ($data->{'dir'} === 'templates' && $fileName !== 'header' && $fileName !== 'footer') {
		$pageSql = "INSERT INTO pages (pagePermalink, pageFileName, pageTheme) VALUES ('$fileName', '$fileName', '$theme')";
		$connect->query($pageSql);

		$insertPermalink = true;
	}

	$data = array(
		'saveFile' => 'true',
		'theme' => $data->{'theme'},
		'dir' => $data->{'dir'},
		'fileName' => $data->{'fileName'},
		'insertPermalink' => $insertPermalink
	);
	echo json_encode($data);
}

if ($request == 'duplicateFile') {
	if ($data->{'file'} !== $data->{'newFileName'}) {
		$file = realpath(__DIR__ ."/../../pyxl-content/themes/".$data->{'theme'}."/".$data->{'dir'})."/".$data->{'file'};
		$newFile = realpath(__DIR__ ."/../../pyxl-content/themes/".$data->{'theme'}."/".$data->{'dir'})."/".$data->{'newFile'};
		$newFileName = $data->{'newFileName'};
		$theme = $data->{'theme'};
		$fileName = $data->{'fileName'};
		
		copy($file, $newFile);
	
		$insertPermalink = false;
		if ($data->{'dir'} === 'templates' && $fileName !== 'header' && $fileName !== 'footer') {
			$pageSql = "INSERT INTO pages (pagePermalink, pageFileName, pageTheme) VALUES ('$newFileName', '$newFileName', '$theme')";
			$connect->query($pageSql);
	
			$insertPermalink = true;
		}
	
		$data = array(
			'saveFile' => 'true',
			'theme' => $data->{'theme'},
			'dir' => $data->{'dir'},
			'fileName' => $newFileName,
			'insertPermalink' => $insertPermalink
		);
	} else {
		$data = array(
			'result' => false
		);
	}

	echo json_encode($data);
}

if ($request == 'renameFile') {
	if ($data->{'file'} !== $data->{'newFileName'}) {
		$file = realpath(__DIR__ ."/../../pyxl-content/themes/".$data->{'theme'}."/".$data->{'dir'})."/".$data->{'file'};
		$newFile = realpath(__DIR__ ."/../../pyxl-content/themes/".$data->{'theme'}."/".$data->{'dir'})."/".$data->{'newFile'};
		$newFileName = $data->{'newFileName'};
		$theme = $data->{'theme'};
		$fileName = $data->{'fileName'};
		
		rename($file, $newFile);
	
		$insertPermalink = false;
		if ($data->{'dir'} === 'templates' && $fileName !== 'header' && $fileName !== 'footer') {
			// Delete Old File Name
			$pageSql = "DELETE FROM pages WHERE pageFileName = '$fileName' AND pageTheme = '$theme'";
			$connect->query($pageSql);
			
			// Save New File Name
			$pageSql = "INSERT INTO pages (pagePermalink, pageFileName, pageTheme) VALUES ('$newFileName', '$newFileName', '$theme')";
			$connect->query($pageSql);
	
			$insertPermalink = true;
		}
	
		$data = array(
			'saveFile' => 'true',
			'theme' => $data->{'theme'},
			'dir' => $data->{'dir'},
			'fileName' => $newFileName,
			'insertPermalink' => $insertPermalink
		);
	} else {
		$data = array(
			'result' => false
		);
	}

	echo json_encode($data);
}

if ($request == 'saveNewTheme') {
	$theme = $data->{'themeName'};
	if (!is_dir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme)) && $data->{'themeName'} != 'images') {
		mkdir(realpath(__DIR__ ."/../../pyxl-content/themes")."/".$theme);
		mkdir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme)."/css/");
		mkdir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme)."/images/");
		mkdir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme)."/templates/");
		mkdir(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme)."/views/");

		// Index View Build
		$indexView = indexView($theme);
		$jsFile = fopen(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/views")."/indexView.js", 'w');
		fwrite($jsFile, $indexView);
		fclose($jsFile);
		unset($indexView);

		// CSS Stylesheet Build
		$styleView = styleView($theme, $data->{'themeAuthor'}, $data->{'themeDesc'}, $data->{'themeTags'});
		$jsFile = fopen(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/css")."/style.css", 'w');
		fwrite($jsFile, $styleView);
		fclose($jsFile);
		unset($styleView);

		// index/header/footer Build
		$header = fopen(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/templates")."/header.html", 'w');
		$index = fopen(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/templates")."/index.html", 'w');
		$footer = fopen(realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/templates")."/footer.html", 'w');
		
		$pageSql = "INSERT INTO pages (pagePermalink, pageFileName, pageTheme) VALUES ('index', 'index', '$theme')";
		$connect->query($pageSql);

		$data = array(
			'themeName' => $theme,
			'result' => true
		);
	} else {
		$data = array(
			'themeName' => $theme,
			'result' => false
		);
	}

	echo json_encode($data);
}

if ($request == 'deleteFile') {
	$theme = $data->{'theme'};
	$file = realpath(__DIR__ ."/../../pyxl-content/themes/".$theme."/".$data->{'dir'}."/".$data->{'file'});

	if (file_exists($file)) {
		unlink($file);
		
		// Delete Page
		$fileName = $data->{'file'};
		$pageSql = "DELETE FROM pages WHERE pageFileName = '$fileName' AND pageTheme = '$theme'";
		$connect->query($pageSql);
		
		$data = array(
			'fileDelete' => true
		);
	} else {
		$data = array(
			'fileDelete' => false
		);
	}

	echo json_encode($data);
}

if ($request == 'deleteTheme') {
	$theme = $data->{'theme'};
	$path = realpath(__DIR__ ."/../../pyxl-content/themes/".$theme);

	if (is_dir($path) === true) {

		$it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
		
		foreach($files as $file) {
			if ($file->isDir()){
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		rmdir($path);

		// Delete Page
		$pageSql = "DELETE FROM pages WHERE pageTheme = '$theme'";
		$connect->query($pageSql);

		$data = array(
			'themeDelete' => true
		);

	} else {
		$data = array(
			'themeDelete' => false
		);
	}
	
	echo json_encode($data);
}