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
 * This file expects an "id" parameter containing a photo ID and responses
 * the thumbnail for it in one of the following cases:
 * - The visitor requesting the thumbnail is logged in and owns the photo
 * - The photo is contained in an album which is publicly visible (status = "public")
 * In all other cases an error 404 is returned.
 */

require_once '../code/App.php';

$tableprefix = $GLOBALS['tableprefix'];

$id = filter_input(INPUT_GET, 'id');
$type = filter_input(INPUT_GET, 'type');
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : false;

$photo = Photos::getPhoto($id, $userid);
if ($photo === null) {
	header("HTTP/1.0 404 Not Found");
	exit;
}
$thumbnailfile = Photos::getMediaDir().$id.($type ? '.'.$type : '');
header('Content-Type: '.$photo[$tableprefix.'media_mimetype']);
header('Content-Length: '.filesize($thumbnailfile));
$handle = fopen($thumbnailfile, 'rb');
if ($handle) {
	while (!feof($handle)) {
		echo fread($handle, 8192);
		ob_flush();
		flush();
	}
	fclose($handle);
}
