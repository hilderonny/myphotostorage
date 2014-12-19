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
 * Handles user login and show a form for that
 */

require_once '../code/App.php';

$redirecturl = filter_input(INPUT_GET, 'redirecturl') ?: '../photos/list.php';
$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$error = false;

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
	$error = Account::login($username, $password);
	if (!$error) {
		// Redirect the user to his photolist or to the redirecturl
		header('Location: '.$redirecturl);
		exit;
	}
} else {
	// First try automatic login
	$cookieusername = filter_input(INPUT_COOKIE, 'username');
	$cookiesecret = filter_input(INPUT_COOKIE, 'secret');
	if ($cookieusername !== null && $cookiesecret !== null) {
		$error = Account::login($cookieusername, $cookiesecret, true);
		if (!$error) {
			// Redirect the user to his photolist
			header('Location: '.$redirecturl);
			exit;
		}
	}
}

?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo __('MyPhotoStorage Login') ?></title>
		<?php Templates::includeTemplate('Head') ?>
    </head>
    <body>
		<form method="post" class="simple login">
			<h1><?php echo __('MyPhotoStorage Login') ?></h1>
			<div>
				<?php if ($error) : ?>
				<p class="notification error"><?php echo $error ?></p>
				<?php endif ?>
				<label><?php echo __('Username') ?></label>
				<input type="text" autocapitalize="off" autocorrect="off" name="username" value="<?php echo $username ?>" maxlength="100" />
				<label><?php echo __('Password') ?></label>
				<input type="password" name="password" maxlength="100" />
				<input type="submit" value="<?php echo __('Login') ?>" />
			</div>
			<div><a href="forgotpassword.php"><?php echo __('Forgot password?') ?></a></div>
			<div><a href="register.php"><?php echo __('Register a new account') ?></a></div>
		</form>
    </body>
</html>