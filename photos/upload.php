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
 * This file is for uploading photos
 */

require_once '../code/App.php';
// Require valid logged in user
Account::requireValidUser();


/**
 * TODO: Link hinzufügen, mit dem man die hochgeladenen Bilder gleich in ein Album packen kann.
 */

?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo __('Upload photos') ?></title>
            <?php Templates::includeTemplate('Head') ?>
            <script src="<?php echo App::getUrl('static/js/Photos.js') ?>"></script>
			<script type="text/javascript">
				function handleSelection(fileinput) {
					Photos.processUpload(fileinput, "ProgressCompletion", "UploadStatus", function() {
						document.getElementById("ToolSelectFilesButton").style.display = "block";
						document.getElementById("ToolCancelButton").style.display = "none";
					});
					document.getElementById("ToolSelectFilesButton").style.display = "none";
					document.getElementById("ToolCancelButton").style.display = "block";
				}
				
				function handleCancel() {
					Photos.cancelUpload();
					document.getElementById("ToolSelectFilesButton").style.display = "block";
					document.getElementById("ToolCancelButton").style.display = "none";
					document.getElementById("UploadStatus").innerHTML = "<?php echo __('Upload cancelled.') ?>";
					document.getElementById("ProgressCompletion").style.width = "0%";
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
		<div class="Tools">
			<button id="ToolSelectFilesButton" onclick="document.getElementById('SelectPhotos').click();return false;"><?php echo __('Select photos') ?></button>
			<button id="ToolCancelButton" style="display:none" onclick="handleCancel();"><?php echo __('Cancel') ?></button>
		</div>
		<div class="Content PhotoUpload">
			<div id="UploadStatus"><?php echo __('Please click on "Select photos" for upload.') ?></div>
			<div class="UploadProgress"><div id="ProgressCompletion"></div></div>
			<input id="SelectPhotos" type="file" multiple="multiple" accept="image/jpeg" onchange="handleSelection(this);" style="display:none" />
		</div>
	</body>
</html>