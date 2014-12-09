<?php

/* 
 * The MIT License
 *
 * Copyright 2014 Ronny Hildebrandt <ronny.hildebrandt@avorium.de>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Handles user login, logout and automatic login and redirects to the 
 * authenticated content. The action parameter can be:
 * "logout": logs out the user, destroys the session and removes the login cookies
 * "register": shows the register form or performs the register post action
 * "login": performs the login post action
 * "forgotpassword": shows the form for requesting the forgot password mail or processes the corresponding post action
 * In any other case the login form is shown
 */

require_once './config/localconfig.inc.php';
require_once './include/helper.inc.php';

$action = filter_input(INPUT_GET, 'action');
if ($action === 'register') { // Register
	if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
		$username = filter_input(INPUT_POST, 'username');
		$email = filter_input(INPUT_POST, 'email');
		$password = filter_input(INPUT_POST, 'password');
		$password2 = filter_input(INPUT_POST, 'password2');
		$registrationerror = Authentication::register($username, $email, $password, $password2);
		if (!$registrationerror) {
			// Redirect the user to his photolist
			header('Location: photos/list.html');
			exit;
		}
	} else {
		$registrationerror = false;
		$username = '';
		$email = '';
		$password = '';
		$password2 = '';
	}
} elseif ($action === 'forgotpassword') { // Forgot password
	if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
		
	} else {
		$forgotpasswordsent = false;
	}
} elseif ($action === 'logout') { // Logout
	Authentication::logout();
	header('Location: ?');
	exit;
} else { // Login
	if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
		$username = filter_input(INPUT_POST, 'username');
		$password = filter_input(INPUT_POST, 'password');
		$loginerror = Authentication::login($username, $password);
		if (!$loginerror) {
			// Redirect the user to his photolist
			header('Location: photos/list.html');
			exit;
		}
	} else {
		// First try automatic login
		$cookieusername = filter_input(INPUT_COOKIE, 'username');
		$cookiesecret = filter_input(INPUT_COOKIE, 'secret');
		if ($cookieusername !== null && $cookiesecret !== null) {
			$loginerror = Authentication::login($cookieusername, $cookiesecret, true);
			if (!$loginerror) {
				// Redirect the user to his photolist
				header('Location: photos/list.html');
				exit;
			} else {
				$username = '';
			}
		} else {
			$loginerror = false;
			$username = '';
		}
	}
}

?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo __('MyPhotoStorage Login') ?></title>
		<link rel="stylesheet" href="static/css/default.css" />
	</head>
	<body>
		<?php if ($action === 'register') : ?>
		<form method="post" action="?action=register">
			<h1><?php echo __('MyPhotoStorage Registration') ?></h1>
			<div>
				<?php if ($registrationerror) : ?>
				<p class="error"><?php echo $registrationerror ?></p>
				<?php endif ?>
				<label><?php echo __('Username') ?></label>
				<input type="text" name="username" value="<?php echo $username ?>" />
				<label><?php echo __('Email address') ?></label>
				<input type="email" name="email" value="<?php echo $email ?>" />
				<label><?php echo __('Password') ?></label>
				<input type="password" name="password" value="<?php echo $password ?>" />
				<label><?php echo __('Repeat password') ?></label>
				<input type="password" name="password2" value="<?php echo $password2 ?>" />
				<input type="submit" value="<?php echo __('Register') ?>" />
			</div>
			<a href="?action=login"><?php echo __('Login') ?></a>
		</form>
		<?php elseif ($action === 'forgotpassword') : ?>
		<form method="post" action="?action=forgotpassword">
			<h1><?php echo __('Forgot password') ?></h1>
			<div>
				<?php if ($forgotpasswordsent) : ?>
				<p class="info"><?php echo __('A password reset link was sent to the email address you typed in. Please check your inbox for further instructions.') ?></p>
				<?php endif ?>
				<p><?php echo __('Please type your email address into the field below and click on "Send" to get a password reset mail.') ?></p>
				<label><?php echo __('Email address') ?></label>
				<input type="email" name="email" />
				<input type="submit" value="<?php echo __('Send') ?>" />
			</div>
			<a href="?action=login"><?php echo __('Login') ?></a>
			<a href="?action=register"><?php echo __('Register') ?></a>
		</form>
		<?php else : ?>
		<form method="post" action="?action=login">
			<h1><?php echo __('MyPhotoStorage Login') ?></h1>
			<div>
				<?php if ($loginerror) : ?>
				<p class="error"><?php echo $loginerror ?></p>
				<?php endif ?>
				<label><?php echo __('Username') ?></label>
				<input type="text" name="username" value="<?php echo $username ?>" />
				<label><?php echo __('Password') ?></label>
				<input type="password" name="password" />
				<input type="submit" value="<?php echo __('Login') ?>" />
				<a href="?action=forgotpassword"><?php echo __('Forgot password?') ?></a>
			</div>
			<a href="?action=register"><?php echo __('Register') ?></a>
		</form>
		<?php endif ?>
	</body>
</html>