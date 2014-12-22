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
 * Handles request new password form
 */

require_once '../code/App.php';

$email = filter_input(INPUT_POST, 'email');
$forgotpasswordsent = false;

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
	$email = filter_input(INPUT_POST, 'email');
	$passwordresetlink = Account::getPasswordResetLink($email);
	if ($passwordresetlink) {
		// Store the link in the global variable so the template file can access it
		$GLOBALS['passwordforgetlink'] = $passwordresetlink;
		ob_start();
		Templates::includeTemplate('ForgotPasswordEmail');
		$content = ob_get_clean();
		mail($email, __('Request for password reset'), $content);
	}
	$forgotpasswordsent = true;
}
?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo __('Request password reset') ?></title>
		<?php Templates::includeTemplate('Head') ?>
    </head>
    <body>
		<form method="post" class="Simple Forgotpassword">
			<h1><?php echo __('Request password reset') ?></h1>
			<div>
				<?php if ($forgotpasswordsent) : ?>
				<p class="notification success"><?php echo __('A password reset link was sent to the email address you typed in. Please check your inbox for further instructions.') ?></p>
				<?php endif ?>
				<p><?php echo __('Please type your email address into the field below and click on "Send" to get a password reset mail.') ?></p>
				<label><?php echo __('Email address') ?></label>
				<input type="email" name="email" />
				<input type="submit" value="<?php echo __('Send') ?>" />
			</div>
			<div><a href="login.php"><?php echo __('Login') ?></a></div>
			<div><a href="register.php"><?php echo __('Register a new account') ?></a></div>
		</form>
    </body>
</html>