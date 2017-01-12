<?php

/*
 *
 * File Name: class.email.php
 * Description: Email sending functionality
 *
 */

// Includes
include_once('../config/class.connect.php');

// SQL Request
$settingsSql = "SELECT * FROM settings";
 
$envProp = $connect->query($settingsSql);
while($info = $envProp->fetch_assoc()){
	$siteEmail = $info['siteEmail'];
	$siteName = $info['siteName'];
	$siteUrl = $info['siteUrl'];
}

function sendEmail($type, $email, $code) {

	// Global
	global $siteEmail;
	global $siteName;
	global $siteUrl;
	
	// Default Headers
	$headers  = "From: ". $siteName ." <". $siteEmail .">\r\n";
	$headers .= "Reply-To: ". $email . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	if ($type == 'register') {
		// Subject
		$subject = $siteName . ": Validate Your Account";

		// Create Activation Code
		$activate = $siteUrl . "/pyxl-core/activate/" . $code . "|" . $email;

		// Message
		$message  = "<div style='background: #222;border-radius: 10px;text-align: center;padding: 50px;'>";
			$message .= "<h2 style='background: #323232;color: #1ab394;margin: 0;font-size: 34px;margin-bottom: 60px;border-radius: 5px;padding: 10px;'>Verify Your Account</h2>";
			$message .= "<p style='color: #eaeaea;font-size: 14px;'>Welcome to ".$siteName.", in order to confirm your account please click on the activation email below.</p>";
			$message .= "<p style='margin: 60px 0;'>";
			$message .= "<a href='".$activate."' style='display: block;margin: 0 auto;width: 150px;padding: 15px;background: #1ab394;font-size: 15px;font-weight: bold;text-decoration: none;border-radius: 5px;border: 1px solid #3ab99f;color: #FFFFFF;' title='Activate Account'>Activate Account</a></p>";
			$message .= "<p style='color: #777;font-size: 11px;margin: 0;'>If you did not register with us then please disregard this email. If you have any questions please send an email to <a href='mailto:".$siteEmail."' style='color:#777'>".$siteEmail."</a>.</p>";
		$message .= "</div>";
	}

	if ($type == 'resendActivate') {
		// Subject
		$subject = $siteName . ": Validate Your Account";

		// Create Activation Code
		$activate = $siteUrl . "/pyxl-core/activate/" . $code . "|" . $email;

		// Message
		$message  = "<div style='background: #222;border-radius: 10px;text-align: center;padding: 50px;'>";
			$message .= "<h2 style='background: #323232;color: #1ab394;margin: 0;font-size: 34px;margin-bottom: 60px;border-radius: 5px;padding: 10px;'>Verify Your Account</h2>";
			$message .= "<p style='color: #eaeaea;font-size: 14px;'>Welcome to ".$siteName.", in order to confirm your account please click on the activation email below.</p>";
			$message .= "<p style='margin: 60px 0;'>";
			$message .= "<a href='".$activate."' style='display: block;margin: 0 auto;width: 150px;padding: 15px;background: #1ab394;font-size: 15px;font-weight: bold;text-decoration: none;border-radius: 5px;border: 1px solid #3ab99f;color: #FFFFFF;' title='Activate Account'>Activate Account</a></p>";
			$message .= "<p style='color: #777;font-size: 11px;margin: 0;'>If you did not register with us then please disregard this email. If you have any questions please send an email to <a href='mailto:".$siteEmail."' style='color:#777'>".$siteEmail."</a>.</p>";
		$message .= "</div>";
	}

	if ($type == 'forgotPasswordReset') {
		// Subject
		$subject = $siteName . ": Forgot Password Instructions";

		// Reset Password Code
		$code = $siteUrl . "/pyxl-core/login/password/" . $code . "|" . $email;

		// Message
		$message  = "<div style='background: #222;border-radius: 10px;text-align: center;padding: 50px;'>";
			$message .= "<h2 style='background: #323232;color: #1ab394;margin: 0;font-size: 34px;margin-bottom: 60px;border-radius: 5px;padding: 10px;'>Forgot Password Instructions</h2>";
			$message .= "<p style='color: #eaeaea;font-size: 14px;'>Your password on ".$siteName." will be reset once you click below.</p>";
			$message .= "<p style='margin: 60px 0;'>";
			$message .= "<a href='".$code."' style='display: block;margin: 0 auto;width: 150px;padding: 15px;background: #1ab394;font-size: 15px;font-weight: bold;text-decoration: none;border-radius: 5px;border: 1px solid #3ab99f;color: #FFFFFF;' title='Reset Password'>Reset Password</a></p>";
			$message .= "<p style='color: #777;font-size: 11px;margin: 0;'>If you did not request to have your password changed then please disregard this email. If you have any questions please send an email to <a href='mailto:".$siteEmail."' style='color:#777'>".$siteEmail."</a>.</p>";
		$message .= "</div>";
	}

	if ($type == 'forgotPasswordComplete') {
		// Subject
		$subject = $siteName . ": Your Password Has Been Reset";

		// Message
		$message  = "<div style='background: #222;border-radius: 10px;text-align: center;padding: 50px;'>";
			$message .= "<h2 style='background: #323232;color: #1ab394;margin: 0;font-size: 34px;margin-bottom: 60px;border-radius: 5px;padding: 10px;'>Password Reset</h2>";
			$message .= "<p style='color: #eaeaea;font-size: 14px;'>This is a confirmation that the password on ".$siteName." has been changed. If you didn't request a password change, try signing in and reset your password if necessary. If you're having trouble, please send and email to <a href='mailto:".$siteEmail."' style='color:#FFF'>".$siteEmail."</a>.</p>";
			$message .= "<p style='color: #777;font-size: 11px;margin: 0;'>If you have any questions please send an email to <a href='mailto:".$siteEmail."' style='color:#777'>".$siteEmail."</a>.</p>";
		$message .= "</div>";
	}
	
	// Fire Email
	if (!empty($email) && !empty($subject) && !empty($message) && !empty($headers)) {
		mail($email, $subject, $message, $headers);
	}
}