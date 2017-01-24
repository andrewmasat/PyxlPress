<?php 
	if(file_exists(realpath(__DIR__ . '/../../pyxl-include/config.php'))) {
		header('Location: http://' . $_SERVER["SERVER_NAME"]);
		exit();
	}
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Install PyxlPress</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" /> -->

		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700">
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../css/animate.css">
		<link rel="stylesheet" type="text/css" href="../css/style.css">
		<script data-main="js/main" src="../js/libs/jquery/jquery-min.js"></script>
		<script data-main="js/main" src="../js/libs/bootstrap/bootstrap-min.js"></script>
		<script data-main="js/main" src="install.js"></script>
	</head>
	<body>
		<div class="stage">
			<div class="installPage">
				<section class="install step1">
					<h1 class="title">Install PyxlPress</h1>
					<div class="alert alert-danger hidden" role="alert"><strong>Install Failed</strong> - Please check your database settings.</div>
					<p>Lets begin installing PyxlPress!</p>
					<p>Please enter your database connection details. If you are not sure, please contact your host. This will install a fresh build to your database.</p>
					<form id="install" class="form-horizontal">
						<div class="form-group">
							<label for="dbname" class="col-sm-3 control-label">Database Name:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dbname" name="dbname">
								<small>The name of the database you want to run PyxlPress in.</small>
							</div>
						</div>
						<div class="form-group">
							<label for="username" class="col-sm-3 control-label">Username:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="username" name="username">
								<small>Your mySQL username</small>
							</div>
						</div>
						<div class="form-group">
							<label for="password" class="col-sm-3 control-label">Password:</label>
							<div class="col-sm-9">
								<input type="password" class="form-control" id="password" name="password">
								<small>...and mySQL password.</small>
							</div>
						</div>
						<div class="form-group">
							<label for="dbhost" class="col-sm-3 control-label">Database Host:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dbhost" name="dbhost" placeholder="localhost">
								<small>You should be able to get this info from your web host, if <em>localhost</em> does not work.</small>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-12">
								<button type="submit" data-loading-text="Installing..." id="submitInstall" class="btn btn-success pull-right" autocomplete="off">Install Now</button>
							</div>
						</div>
					</form>
				</section>
				<section class="install step2 hidden">
					<h1 class="title">PyxlPress Settings</h1>
					<p>We are connected! Now lets get things set up for your PyxlPress site.</p>
					<form id="settings" class="form-horizontal">
						<div class="form-group">
							<label for="siteName" class="col-sm-3 control-label">Site Name:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="siteName" name="siteName" placeholder="PyxlPress Site">
							</div>
						</div>
						<div class="form-group">
							<label for="siteEmail" class="col-sm-3 control-label">Site Email:</label>
							<div class="col-sm-9">
								<input type="email" class="form-control" id="siteEmail" name="siteEmail" placeholder="PyxlPress Email">
							</div>
						</div>
						<div class="form-group">
							<label for="siteUrl" class="col-sm-3 control-label">Site Address:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="siteUrl" name="siteUrl">
								<small>Recommended to leave this alone unless you are an advanced user.</small>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-12">
								<button type="submit" data-loading-text="Saving..." id="submitSettings" class="btn btn-success pull-right" autocomplete="off">Save Settings</button>
							</div>
						</div>
					</form>
				</section>
				<section class="install step3 hidden">
					<h1 class="title">Create your account</h1>
					<p>Awesome! Your install is complete, now it is time to setup your Pxylpress account.</p>
					<form id="account" class="form-horizontal">
						<div class="form-group">
							<label for="username" class="col-sm-3 control-label">Username:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="username" name="username">
							</div>
						</div>
						<div class="form-group">
							<label for="email" class="col-sm-3 control-label">Account Email:</label>
							<div class="col-sm-9">
								<input type="email" class="form-control" id="email" name="email">
							</div>
						</div>
						<div class="form-group">
							<label for="password" class="col-sm-3 control-label">Password:</label>
							<div class="col-sm-9">
								<input type="password" class="form-control" id="password" name="password">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-12">
								<button type="submit" data-loading-text="Creating Account..." id="submitAccount" class="btn btn-success pull-right" autocomplete="off">Create Account</button>
							</div>
						</div>
					</form>
				</section>
				<section class="install finish hidden">
					<h1 class="title">Installation Complete</h1>
					<p>Your account is now created and your PyxlPress install is complete!</p>
					<p>When you are ready, you can return to your site's home page and log in.</p>
					<button class="btn btn-success pull-right returnHome">Return Home</button>
				</section>
			</div>
		</div>
	</body>
</html>