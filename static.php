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
 * Serves the static content and performs translations in js and css files.
 * In these files all markers with ++##TEXT##-- will be translated.
 */

require_once './include/helper.inc.php';

// Construct the absolute file path
$staticfilename = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').filter_input(INPUT_SERVER, 'REQUEST_URI');
if (!file_exists($staticfilename)) {
	header("HTTP/1.0 404 Not Found");
	exit;
}
// Determine the mime type
if (substr($staticfilename, -4) === '.css') {
	$mimetype = 'text/css';
} elseif (substr($staticfilename, -3) === '.js') {
	$mimetype = 'text/javascript';
} else {
	$mimetype = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $staticfilename);
}
// Translate the content by replacing the pattern ++##TEXT##-- with translated 
// values
$content = file_get_contents($staticfilename);
$translatedcontent = preg_replace_callback('/\+\+\#\#(.*?)\#\#\-\-/', function($match) {
	return __($match[1]);
}, $content);
// Send result to browser
header('Content-Type: '.$mimetype);
echo $translatedcontent;