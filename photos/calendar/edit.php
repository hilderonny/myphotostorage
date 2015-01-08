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

require_once '../../code/App.php';
// Require valid logged in user
Account::requireValidUser();

?><!DOCTYPE html>
<html class="CalendarPage">
    <head>
<!--		<meta http-equiv="refresh" content="5" />-->
        <title><?php echo __('Edit calendar') ?></title>
        <?php Templates::includeTemplate('Head') ?>
        <script src="<?php echo App::getUrl('static/js/Dialog.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Menu.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Photos.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Calendar.js') ?>"></script>
        <script type="text/javascript">
            // The calendar page is loaded when the document was loaded
            window.addEventListener('load', function() {
				Calendar.init();
                Calendar.renderCalendarPage('Content');
            });
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
            <button class="Close" />
            <button class="Save" />
            <button class="Settings" />
            <button class="Delete" style="float:right;display:inherit;" />
        </div>
        <div id="Content" class="Content"></div>
    </body>
</html>