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
 * Handles user registration and show a form for that
 */

require_once '../code/App.php';

$redirecturl = filter_input(INPUT_GET, 'redirecturl') ?: '../photos/list.php';
$username = filter_input(INPUT_POST, 'username');
$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');
$password2 = filter_input(INPUT_POST, 'password2');
$error = false;

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
    $error = Account::register($username, $email, $password, $password2);
    if (!$error) {
		$error = Account::login($username, $password);
	    if (!$error) {
			// Redirect the user to his photolist or to the redirecturl
			header('Location: '.$redirecturl);
			exit;
		}
    }
}

?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo __('Account registration') ?></title>
		<?php Templates::includeTemplate('Head') ?>
    </head>
    <body>
        <form method="post" class="simple register">
            <h1><?php echo __('Register a new account') ?></h1>
            <div>
                <?php if ($error) : ?>
                <p class="notification error"><?php echo $error ?></p>
                <?php endif ?>
                <label><?php echo __('Username') ?></label>
                <input type="text" autocapitalize="off" autocorrect="off" name="username" value="<?php echo $username ?>" />
                <label><?php echo __('Email address') ?></label>
                <input type="email" name="email" value="<?php echo $email ?>" />
                <label><?php echo __('Password') ?></label>
                <input type="password" name="password" value="<?php echo $password ?>" />
                <label><?php echo __('Repeat password') ?></label>
                <input type="password" name="password2" value="<?php echo $password2 ?>" />
                <input type="submit" value="<?php echo __('Create account') ?>" />
            </div>
            <div><a href="login.php"><?php echo __('Login') ?></a></div>
        </form>
    </body>
</html>