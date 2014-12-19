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
 * Handles password reset and show a form for that
 */

require_once '../code/App.php';

$key = filter_input(INPUT_GET, 'key');
$password = filter_input(INPUT_POST, 'password');
$password2 = filter_input(INPUT_POST, 'password2');
$error = false;
$passwordwasreset = false;

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
	$error = Account::resetPassword($key, $password, $password2);
	if (!$error) {
		$passwordwasreset = true;
	}
}

?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo __('Reset password') ?></title>
		<?php Templates::includeTemplate('Head') ?>
    </head>
    <body>
		<form method="post" class="simple resetpassword">
			<h1><?php echo __('Reset password') ?></h1>
			<div>
				<?php if ($passwordwasreset) : ?>
				<p class="notification success"><?php echo __('Your password was reset. You can now login with your username and your new password.') ?></p>
				<?php else : ?>
				<?php   if ($error) : ?>
				<p class="notification error"><?php echo $error ?></p>
				<?php   endif ?>
				<p><?php echo __('Please type your new password into the fields below.') ?></p>
				<label><?php echo __('Password') ?></label>
				<input type="password" name="password" maxlength="100" />
				<label><?php echo __('Repeat password') ?></label>
				<input type="password" name="password2" maxlength="100" />
				<input type="submit" value="<?php echo __('Send') ?>" />
				<?php endif ?>
			</div>
            <div><a href="login.php"><?php echo __('Login') ?></a></div>
			<div><a href="register.php"><?php echo __('Register a new account') ?></a></div>
		</form>
    </body>
</html>