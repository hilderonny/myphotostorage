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

require_once './include/helper.inc.php';

// Handle postbacks for form
if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
	// Form was posted back
	$databasehost = filter_input(INPUT_POST, 'databasehost');
	$databaseusername = filter_input(INPUT_POST, 'databaseusername');
	$databasepassword = filter_input(INPUT_POST, 'databasepassword');
	$databasename = filter_input(INPUT_POST, 'databasename');
	$tableprefix = filter_input(INPUT_POST, 'tableprefix');
	$GLOBALS['databasehost'] = $databasehost;
	$GLOBALS['databaseusername'] = $databaseusername;
	$GLOBALS['databasepassword'] = $databasepassword;
	$GLOBALS['databasename'] = $databasename;
	$GLOBALS['tableprefix'] = $tableprefix;
	// Check database connection
	$databaseerror = !Install::canAccessDatabase();
	// Store localconfig file
	$installationprogress = Install::createLocalConfig();
	// Perform the database installation
	$installationprogress .= Install::createAndUpdateTables($tableprefix);
} else {
	// Single GET page call
	$databasehost = 'localhost';
	$databaseusername = '';
	$databasepassword = '';
	$databasename = 'myphotostorage';
	$tableprefix = '';
	$databaseerror = false;
	$installationprogress = false;
}

// Check file permissions
$candeleteinstallsript = Install::canDeleteInstallScript();
$canwritelocalconfig = Install::canWriteLocalConfig();
$canwritemediadir = Install::canWriteMediaDir();
$ispostgresavailable = Install::isPostgresAvailable();


?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo __('Install MyPhotoStorage') ?></title>
	</head>
	<body>
		<h1><?php echo __('Install MyPhotoStorage') ?></h1>
		<form method="post" action="">
			<h2><?php echo __('File system check') ?></h2>
			<div>
				<?php if ($candeleteinstallsript) : ?>
				<p class="success"><?php echo __('Install script can be deleted.') ?></p>
				<?php else : ?>
				<p class="error"><?php echo sprintf(__('Install script cannot be deleted. Please make sure that the webserver process has write permissions to the file %s'), Install::$installFileName) ?></p>
				<?php endif ?>
				<?php if ($canwritelocalconfig) : ?>
				<p class="success"><?php echo __('Config file is writeable.') ?></p>
				<?php else : ?>
				<p class="error"><?php echo sprintf(__('Config file is not writeable. Please make sure that the webserver process has write permissions to the file %s'), Install::$localConfigFileName) ?></p>
				<?php endif ?>
				<?php if ($canwritemediadir) : ?>
				<p class="success"><?php echo __('Media directory is writeable.') ?></p>
				<?php else : ?>
				<p class="error"><?php echo sprintf(__('Media directory is not writeable. Please make sure that the webserver process has write permissions to the directory %s'), Install::$mediaDir) ?></p>
				<?php endif ?>
			</div>
			<h2><?php echo __('Database connection') ?></h2>
			<div>
				<?php if ($ispostgresavailable) : ?>
				<p class="success"><?php echo __('PostgreSQL is available.') ?></p>
				<label><?php echo __('Database host') ?></label>
				<input type="text" name="databasehost" value="<?php echo $databasehost ?>" />
				<label><?php echo __('Database username') ?></label>
				<input type="text" name="databaseusername" value="<?php echo $databaseusername ?>" />
				<label><?php echo __('Database password') ?></label>
				<input type="text" name="databasepassword" value="<?php echo $databasepassword ?>" />
				<label><?php echo __('Database name') ?></label>
				<input type="text" name="databasename" value="<?php echo $databasename ?>" />
				<label><?php echo __('Table prefix') ?></label>
				<input type="text" name="tableprefix" value="<?php echo $tableprefix ?>" />
				<input type="submit" value="<?php echo __('Install') ?>" />
				<?php else : ?>
				<p class="error"><?php echo __('The PostgreSQL PHP extension is not available. Please install it. On Debian you can use "sudo apt-get install php5-pgsql"') ?></p>
				<?php endif ?>
				<?php if ($databaseerror) : ?>
				<p class="error"><?php echo __('Cannot access database. Please check the settings above.') ?></p>
				<?php else : ?>
				<p class="success"><?php echo __('Database connection succeeded.') ?></p>
				<?php endif ?>
			</div>
			<?php if ($installationprogress) : ?>
			<h2><?php echo __('Installation progress') ?></h2>
			<div>
				<?php echo $installationprogress ?>
			</div>
			<?php endif ?>
		</form>
	</body>
</html>