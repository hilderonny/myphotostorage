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
 * Account settings for own user account like email change,
 * password change or account deletion.
 */

require_once '../code/App.php';
// Require valid logged in user
Account::requireValidUser();

$userid = $_SESSION['userid'];
$account = Account::getUser($userid);

$newemail = filter_input(INPUT_POST, 'email');
$newpassword = filter_input(INPUT_POST, 'password');
$newpassword2 = filter_input(INPUT_POST, 'password2');
$error = false;
$success = false;

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
	$error = Account::updateUser($userid, $newemail, $newpassword, $newpassword2);
    if (!$error) {
		$success = __('Your changes have been saved.');
    }
}

?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo __('Account settings') ?></title>
		<?php Templates::includeTemplate('Head') ?>
        <script src="<?php echo App::getUrl('static/js/Helper.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Dialog.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Menu.js') ?>"></script>
		<script type="text/javascript">
			function handleDeleteClick() {
				Dialog.confirm("<?php echo __('Do you really want to delete your account and all stored data?') ?>", function(shoulddelete) {
					if (shoulddelete) {
						Helper.doRequest("deleteAccount", null, function(response) {
							console.log(response);
							if (response === "OK") {
								Dialog.info("<?php echo __('Your account was deleted.') ?>", function() {
									window.location.href = "../";
								});
							}
						});
					}
				});
			}
		</script>
    </head>
    <body>
        <div class="Menu">
            <button onclick="Menu.handleClick(this);"></button>
            <div>
                <?php Templates::includeTemplate('MainMenu') ?>
                <?php Templates::includeTemplate('PhotoMenu') ?>
            </div>
        </div>
		<div class="Content">
			<form method="post" class="Simple Accountsettings">
				<h1><?php echo __('Account settings') ?></h1>
				<div>
					<?php if ($error) : ?>
					<p class="notification error"><?php echo $error ?></p>
					<?php endif ?>
					<?php if ($success) : ?>
					<p class="notification success"><?php echo $success ?></p>
					<?php endif ?>
					<label><?php echo __('Username') ?></label>
					<span><?php echo $account['username'] ?></span>
					<label><?php echo __('Email address') ?></label>
					<input type="email" name="email" value="<?php echo $account['email'] ?>" maxlength="100" />
					<p><?php echo __('If you want to change your password, please input a new password into the fields below and click on "Save". Otherwise leave the fields empty.') ?></p>
					<label><?php echo __('Password') ?></label>
					<input type="password" name="password" value="<?php echo $newpassword ?>" maxlength="100" />
					<label><?php echo __('Repeat password') ?></label>
					<input type="password" name="password2" value="<?php echo $newpassword2 ?>" maxlength="100" />
					<input type="submit" value="<?php echo __('Save') ?>" />
					<button class="Red" onclick="handleDeleteClick();return false;"><?php echo __('Delete account') ?></button>
				</div>
			</form>
		</div>
    </body>
</html>