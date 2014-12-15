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
 * This file contains helper functions which make the developers life easier.
 * It also starts or resumes the session. TRhis file must be included in every
 * page.
 * This file also includes the localconfig.inc.php file.
 */

// Set the include path to the base directory of the installation
set_include_path(get_include_path().PATH_SEPARATOR .dirname(__DIR__));
require_once 'config/localconfig.inc.php';

/**
 * Translates the given string into the language currently used by the visitor.
 * 
 * @param string $str String to translate into the current language
 * @return string String translated into the current language
 */
function __($str) {
	return Localization::translate($str);
}

/*
 * Autoloader. Each call to a class searches for a PHP file in the code
 * directory with the same name and requires it. If the class contains a
 * static __init__ function, the function gets called.
 */
spl_autoload_register(function ($class) {
	require_once 'code/'.$class.'.php';
	if (is_callable($class.'::__init__')) {
		$class::__init__();
	}
});

session_start();

/**
 * Class with some base functions
 */
class App {
	
	private static $baseurl;
	
	/**
	 * Returns the base URL of the application with trailing slash.
	 */
	static function getBaseUrl() {
		if (!self::$baseurl) {
			$relativebaseurl = substr(dirname(dirname(__FILE__)), strlen(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT')));
			self::$baseurl = filter_input(INPUT_SERVER, 'REQUEST_SCHEME').'://'.filter_input(INPUT_SERVER, 'HTTP_HOST').$relativebaseurl.'/';
		}
		return self::$baseurl;
	}
	
	/**
	 * For the given relative URL a full URL string is returned.
	 * @param string $relativefrombase Relative URL withour leading slash
	 * @return string Full URL
	 */
	static function getUrl($relativefrombase) {
		return self::getBaseUrl().$relativefrombase;
	}
}