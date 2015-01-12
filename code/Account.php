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
 * Handles login, logout, registration and password forgetting of users.
 * Also manages user accounts.
 *
 * @author Ronny Hildebrandt <ronny.hildebrandt@avorium.de>
 */
class Account {
	
	/**
	 * Logs the given user into the system and sets the session cookies.
	 * If the login fails, a localized error message is returned.
	 * 
	 * @param string $username Username of the user
	 * @param string $password Password for the user
	 * @param boolean $passwordishashed Whe true the password is interpreted as hashed (comes from a cookie)
	 * @return string False on success or an error message
	 */
	static function login($username, $password, $passwordishashed = false) {
		$tableprefix = $GLOBALS['tableprefix'];
		$escapedusername = Persistence::escape($username);
		$userquery = sprintf('select '.$tableprefix.'users_id, '.$tableprefix.'users_username, '.$tableprefix.'users_password from '.$tableprefix.'users where '.$tableprefix.'users_username=\'%s\'', $escapedusername);
		$users = Persistence::query($userquery);
		if (count($users) < 1) {
			sleep(2); // Against brute force attacks
			self::logout();
			return __('Username or password incorrect.');
		}
		$user = $users[0];
		if ($passwordishashed) {
			// The hashed password coming from the cookies is a hash of the username plus the hashed password
			// I think this could be secure enough, isn't it?
			if (!password_verify($username.$user[$tableprefix.'users_password'], $password)) {
				sleep(2); // Against brute force attacks
				self::logout();
				return __('Username or password incorrect.');
			}
		} else {
			if (!password_verify($password, $user[$tableprefix.'users_password'])) {
				sleep(2); // Against brute force attacks
				self::logout();
				return __('Username or password incorrect.');
			}
		}
		// At this point the authentication is correct, so set the cookies and session variables
		// Cookie lifetime is 30 days.
		setcookie('username', $username, time()+60*60*24*30);
		setcookie('secret', password_hash($username.$user[$tableprefix.'users_password'], PASSWORD_DEFAULT), time() + 60 * 60 * 24 * 30);
		$_SESSION['userid'] = $user[$tableprefix.'users_id'];
		return false;
	}
	
	/**
	 * Logs out the user by invalidating the session and deleting all session
	 * cookies
	 */
	static function logout() {
		setcookie('username', '', -1);
		setcookie('secret', '', -1);
		unset($_SESSION['userid']);
		session_destroy();
		session_regenerate_id(true);
		session_start();
	}
	
	/**
	 * Performs a registration for an user. When the username or the email address
	 * als already in use, a translated error message is returned.
	 * When the two passwords do not match, a translated error message is also returned.
	 * 
	 * @param string $username Username to register
	 * @param string $email Email address
	 * @param string $password Password for the new account
	 * @param string $password2 Same password again
	 * @return string False on succes or a translated error message.
	 */
	static function register($username, $email, $password, $password2) {
		$tableprefix = $GLOBALS['tableprefix'];
		if (empty($username) || empty($email) || empty($password)) {
			return __('Username, email address or password cannot be empty.');
		}
		if ($password !== $password2) {
			return __('The passwords do not match.');
		}
		$escapedusername = Persistence::escape($username);
		$escapedemail = Persistence::escape($email);
		$existingusersquery = sprintf('select '.$tableprefix.'users_username from '.$tableprefix.'users where '.$tableprefix.'users_username=\'%s\' or '.$tableprefix.'users_email=\'%s\'', $escapedusername, $escapedemail);
		$existingusers = Persistence::query($existingusersquery);
		if (count($existingusers) > 0) {
			return __('The username or email address is already in use. Please choose other ones.');
		}
		$hashedpassword = password_hash($password, PASSWORD_DEFAULT);
		$escapedpassword = Persistence::escape($hashedpassword);
		$insertquery = sprintf('insert into '.$tableprefix.'users ('.$tableprefix.'users_username, '.$tableprefix.'users_email, '.$tableprefix.'users_password) values(\'%s\',\'%s\',\'%s\')', $escapedusername, $escapedemail, $escapedpassword);
		Persistence::query($insertquery);
		return false;
	}
	
	/**
	 * Constructs and returns a password reset link for the user identified by
	 * the given email address. Returns false when there is no user with that
	 * email address.
	 * 
	 * @param string $email Email address of the user
	 * @return string Password reset link or false.
	 */
	static function getPasswordResetLink($email) {
		$tableprefix = $GLOBALS['tableprefix'];
        $escapedemail = Persistence::escape($email);
        $userquery = sprintf('select '.$tableprefix.'users_id, '.$tableprefix.'users_username, '.$tableprefix.'users_password from '.$tableprefix.'users where '.$tableprefix.'users_email=\'%s\'', $escapedemail);
        $users = Persistence::query($userquery);
        if (count($users) < 1) {
            return false;
        }
		$passwordhash = password_hash($users[0][$tableprefix.'users_username'].$users[0][$tableprefix.'users_password'], PASSWORD_DEFAULT);
		$passwordforgetlink = App::getUrl('account/resetpassword.php?key='.urlencode($passwordhash));
		return $passwordforgetlink;
	}
	
	/**
	 * Sets the password of the user with the given key to the new ones.
	 * Before that the passwords are checked for equality.
	 * 
	 * @param string $key Key the user got with the password reset mail
	 * @param string $password New password
	 * @param string $password2 New password again
	 * @return boolean False, when the password was reset, a translated errorstring otherwise
	 */
	static function resetPassword($key, $password, $password2) {
		$tableprefix = $GLOBALS['tableprefix'];
		if ($password !== $password2) {
			return __('The passwords do not match.');
		} else {
			$users = Persistence::query('select * from '.$tableprefix.'users');
			for ($i = 0; $i < count($users); $i++) {
				$user = $users[$i];
				if (password_verify($user[$tableprefix.'users_username'].$user[$tableprefix.'users_password'], $key)) {
					$hashedpassword = password_hash($password, PASSWORD_DEFAULT);
					$escapedpassword = Persistence::escape($hashedpassword);
					$updatequery = sprintf('update '.$tableprefix.'users set '.$tableprefix.'users_password = \'%s\' where '.$tableprefix.'users_id = %s', $escapedpassword, $user[$tableprefix.'users_id']);
					Persistence::query($updatequery);
					return false;
				}
			}
		}
		return __('The password reset link is not valid.');
	}
	
	/**
	 * Is called from every page which requires a logged in user
	 * and redirects to the login form when no user was logged in.
	 * The login form gets a redirect URL to the calling page.
	 */
	static function requireValidUser() {
		if (!isset($_SESSION['userid'])) {
			Account::logout();
			header('Location: '.App::getUrl('account/login.php?redirecturl='.urlencode(filter_input(INPUT_SERVER, 'REQUEST_URI'))));
		}
	}
	
	/**
	 * Returns the username and email address for the user with the given ID
	 * @param string $userid ID of the user to retrieve
	 * @return array Array containing username and email of the user or false when no user
	 * with the given ID exists.
	 */
	static function getUser($userid) {
		$tableprefix = $GLOBALS['tableprefix'];
        $userquery = sprintf('select '.$tableprefix.'users_username, '.$tableprefix.'users_email from '.$tableprefix.'users where '.$tableprefix.'users_id=%s', Persistence::escape($userid));
        $users = Persistence::query($userquery);
        if (count($users) < 1) {
            return false;
        }
		$account = [
			'username' => $users[0][$tableprefix.'users_username'],
			'email' => $users[0][$tableprefix.'users_email']
		];
		return $account;
	}
	
	/**
	 * Updates the email or password of an user.
	 * 
	 * @param int $userid ID of the user to change
	 * @param string $newemail New email of the user. Must not be the email of another user.
	 * @param string $newpassword New password for the user. If not set, the password will not be changed
	 * @param string $newpassword2 New password again. Must match $newpassword
	 * @return string Error message or false when all was done well.
	 */
	static function updateUser($userid, $newemail, $newpassword, $newpassword2) {
		$tableprefix = $GLOBALS['tableprefix'];
		$escapeduserid = Persistence::escape($userid);
        $userquery = sprintf('select '.$tableprefix.'users_username, '.$tableprefix.'users_email from '.$tableprefix.'users where '.$tableprefix.'users_id=%s', $escapeduserid);
        $users = Persistence::query($userquery);
        if (count($users) < 1) {
            return false;
		}
		$user = $users[0];
		// Check whether the email changed and check if the new email is assigned to another user.
		$escapednewemail = Persistence::escape($newemail);
		if ($user[$tableprefix.'users_email'] !== $newemail) {
			$existingusers = Persistence::query('select '.$tableprefix.'users_id from '.$tableprefix.'users where '.$tableprefix.'users_email=\''.$escapednewemail.'\' and '.$tableprefix.'users_id != '.$escapeduserid);
			if (count($existingusers) > 0) {
				return __('An user with the given email address already exists. Please choose another one.');
			}
			$updatequery = sprintf('update '.$tableprefix.'users set '.$tableprefix.'users_email = \'%s\' where '.$tableprefix.'users_id = %s', $escapednewemail, $escapeduserid);
			Persistence::query($updatequery);
		}
		// Check whether the password needs to be changed
		if ($newpassword || $newpassword2) {
			if ($newpassword !== $newpassword2) {
				return __('The passwords do not match.');
			}
			$hashedpassword = password_hash($newpassword, PASSWORD_DEFAULT);
			$escapedpassword = Persistence::escape($hashedpassword);
			$updatequery = sprintf('update '.$tableprefix.'users set '.$tableprefix.'users_password = \'%s\' where '.$tableprefix.'users_id = %s', $escapedpassword, $escapeduserid);
			Persistence::query($updatequery);
		}
	}
	
	static function deleteUser($userid) {
		$tableprefix = $GLOBALS['tableprefix'];
		$escapeduserid = Persistence::escape($userid);
		// Delete media files
		$query = '
			select '.$tableprefix.'media_id
			from '.$tableprefix.'media
			where '.$tableprefix.'media_owner_users_id = '.$escapeduserid.'
			';
		$photos = Persistence::query($query);
		if (count($photos) < 1) {
			return;
		}
		for ($i = 0; $i < count($photos); $i++) {
			// Delete files
			unlink(Photos::getMediaDir().$photos[$i][$tableprefix.'media_id'].'.preview');
			unlink(Photos::getMediaDir().$photos[$i][$tableprefix.'media_id'].'.thumb');
			unlink(Photos::getMediaDir().$photos[$i][$tableprefix.'media_id']);
		}
		// Delete user from database. Foreign keys handle the deletion of appended meta data
		$deletequery = 'delete from '.$tableprefix.'users where '.$tableprefix.'users_id = '.$escapeduserid;
		Persistence::query($deletequery);
		self::logout();
		return "OK";
	}
}
