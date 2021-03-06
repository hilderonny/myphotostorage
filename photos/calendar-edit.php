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
 * This page is for editing calendars.
 */

require_once '../code/App.php';
// Require valid logged in user
Account::requireValidUser();

?><!DOCTYPE html>
<html class="CalendarPage">
    <head>
<!--		<meta http-equiv="refresh" content="5" />-->
        <title><?php echo __('Edit calendar') ?></title>
        <?php Templates::includeTemplate('Head') ?>
        <script src="<?php echo App::getUrl('static/js/Helper.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Dialog.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Menu.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Photos.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Calendar.js') ?>"></script>
        <script type="text/javascript">
            // The calendar page is loaded when the document was loaded
            window.addEventListener('load', function() {
				<?php if ($id = filter_input(INPUT_GET, 'id')) : ?>
				Calendar.load(<?php echo $id ?>, 'Content');
				<?php else : ?>
				Calendar.init('Content');
				<?php endif ?>
            });
			function handleDeleteClick() {
				Dialog.confirm('<?php echo __('Do you really want to delete this calendar?') ?>', function(reallydelete) {
					if (reallydelete) {
						Calendar.delete(function() {
							window.location.href = 'calendar-list.php';
						});
					}
				});
			}
			function handleSaveClick() {
				Calendar.save();
			}
			function handleCloseClick() {
				if (Calendar.ischanged) {
					Dialog.confirm('<?php echo __('Do you want to close the calendar without saving the changes?') ?>', function(forceclose) {
						if (forceclose) {
							window.location.href = 'calendar-list.php';
						}
					});
				} else {
					window.location.href = 'calendar-list.php';
				}
			}
			function handleSettingsClick() {
				Calendar.showSettings();
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
            <button class="Close" onclick="handleCloseClick();" />
            <button class="Save" onclick="handleSaveClick();" />
            <button class="Settings" onclick="handleSettingsClick();" />
            <button class="Delete" onclick="handleDeleteClick();" style="float:right;display:inherit;" />
        </div>
        <div id="Content" class="Content"></div>
    </body>
</html>
