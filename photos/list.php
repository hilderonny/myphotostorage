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
 * This page lists all media of the user in a descending date order.
 * The media files will be loaded on demand when the user scrolls.
 */

require_once '../code/App.php';
// Require valid logged in user
Account::requireValidUser();

?><!DOCTYPE html>
<html class="PhotoList">
    <head>
        <title><?php echo __('All photos') ?></title>
        <?php Templates::includeTemplate('Head') ?>
        <script src="<?php echo App::getUrl('static/js/Dialog.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Menu.js') ?>"></script>
        <script src="<?php echo App::getUrl('static/js/Photos.js') ?>"></script>
        <script type="text/javascript">
            // The photos list is loaded after showing the page via AJAX.
            window.addEventListener('load', function() {
                Photos.getList('PhotoList', function(selectcount) {
                    var deletebutton = document.getElementById('DeleteButton');
                    deletebutton.style.display = selectcount > 0 ? 'block' : 'none';
                });
            });
            
            /**
             * Triggered when the user clicks on delete after selcting some
             * files. Shows a confirm dialog and then starts the deletion
             * process file after file. So the user can stop the deletion
             * process when deleting many files.
             */
            function handleDelete() {
                var list = document.getElementById('PhotoList');
                var onephototemplate = '<?php echo __('Do you really want to delete this photo?') ?>';
                var multipmephotostemplate = '<?php echo __('Do you really want to delete {0} photos?') ?>';
                var templatetouse = list.selectedImageCount > 1 ? multipmephotostemplate.replace('{0}', list.selectedImageCount) : onephototemplate;
                Dialog.confirm(templatetouse, function(confirm) {
                    if (confirm) {
                        Photos.deleteSelectedPhotos(function(alldeleted) {
                            if (!alldeleted) {
                                return;
                            }
                            var deletebutton = document.getElementById('DeleteButton');
                            deletebutton.style.display = 'none';
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
        <div class="Tools">
            <button class="Select" onclick="Photos.handleSelect(this);"><?php echo __('Select') ?></button>
            <div class="ToolsZoom">
                <input type="range" min="0" max="100" value="100" oninput="Photos.zoom(this.value)" onchange="Photos.zoom(this.value)" />
            </div>
            <button id="ShareButton" class="Share" />
            <button id="DeleteButton" class="Delete" onclick="handleDelete();" />
        </div>
        <div id="PhotoList" class="Content PhotoList"></div>
    </body>
</html>