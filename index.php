<?php include_once(realpath(__DIR__ . "/pyxl-include/general/class.initPage.php")); ?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php echo $siteName; ?></title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />

		<link href='https://fonts.googleapis.com/css?family=Lato:400,300,700' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<?php if ($incCoreStyles) { ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $siteUrl; ?>/pyxl-core/css/bootstrap.min.css">
			<link rel="stylesheet" type="text/css" href="<?php echo $siteUrl; ?>/pyxl-core/css/animate.css">
		<?php } ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $siteUrl; ?>/pyxl-content/themes/<?php echo $theme; ?>/css/style.css">
		<script data-main="<?php echo $siteUrl; ?>/pyxl-core/js/main" src="<?php echo $siteUrl; ?>/pyxl-core/js/libs/require/require.js"></script>
	</head>
	<body>
		<div class="stage" data-url="<?php echo $siteUrl; ?>"></div>

		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

			ga('create', 'UA-8180209-20', 'auto');
			ga('send', 'pageview');

		</script>
	</body>
</html>