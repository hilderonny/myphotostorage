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
		$escapedusername = Persistence::escape($username);
		$userquery = sprintf('select users_id, users_username, users_password from users where users_username=\'%s\'', $escapedusername);
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
			if (password_verify($username.$user['users_password'], $password)) {
				sleep(2); // Against brute force attacks
				self::logout();
				return __('Username or password incorrect.');
			}
		} else {
			if (!password_verify($password, $user['users_password'])) {
				sleep(2); // Against brute force attacks
				self::logout();
				return __('Username or password incorrect.');
			}
		}
		// At this point the authentication is correct, so set the cookies and session variables
		// Cookie lifetime is 30 days.
		setcookie('username', $username, time()+60*60*24*30);
		setcookie('secret', password_hash($username.$user['users_password'], PASSWORD_DEFAULT), time() + 60 * 60 * 24 * 30);
		$_SESSION['userid'] = $user['users_id'];
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
		if (empty($username) || empty($email) || empty($password)) {
			return __('Username, email address or password cannot be empty.');
		}
		if ($password !== $password2) {
			return __('The passwords do not match.');
		}
		$escapedusername = Persistence::escape($username);
		$escapedemail = Persistence::escape($email);
		$existingusersquery = sprintf('select users_username from users where users_username=\'%s\' or users_email=\'%s\'', $escapedusername, $escapedemail);
		$existingusers = Persistence::query($existingusersquery);
		if (count($existingusers) > 0) {
			return __('The username or email address is already in use. Please choose other ones.');
		}
		$hashedpassword = password_hash($password, PASSWORD_DEFAULT);
		$escapedpassword = Persistence::escape($hashedpassword);
		$insertquery = sprintf('insert into users (users_username, users_email, users_password) values(\'%s\',\'%s\',\'%s\')', $escapedusername, $escapedemail, $escapedpassword);
		Persistence::query($insertquery);
		return false;
	}
        
    /**
     * Returns an user identified by his email. Can be false when no user
     * with the given email exists.
     * 
     * @param string $email Email of the user to get
     */
    static function getUserByEmail($email) {
        $escapedemail = Persistence::escape($email);
        $userquery = sprintf('select users_id, users_username, users_password from users where users_email=\'%s\'', $escapedemail);
        $users = Persistence::query($userquery);
        if (count($users) < 1) {
            return false;
        }
        return $users[0];
    }
    
    /**
     * Returns a list of all users, unordered.
     * Used for validating password reset link.
     * 
     * @return array List of all users
     */
    static function getAllUsers() {
        return Persistence::query('select * from users');
    }
    
    /**
     * Sets the password for an user. Used by password reset form.
     * 
     * @param string $userid Id of the user to set the password for
     * @param string $password Password to set
     */
    static function setUserPassword($userid, $password) {
        $hashedpassword = password_hash($password, PASSWORD_DEFAULT);
        $data = [
            'users_password' => Persistence::escape($hashedpassword)
        ];
        Persistence::update('users', $data, $userid);
    }
}
