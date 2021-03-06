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
 * This file is used for installation.
 */

require_once './code/App.php';

// Check file permissions
$candeleteinstallsript = Install::canDeleteInstallScript();
$canwritelocalconfig = Install::canWriteLocalConfig();
$canwritemediadir = Install::canWriteMediaDir();
$canwritelocaledir = Install::canWriteLocaleDir();
$isdatabaseavailable = Install::isPostgresAvailable();
$isgdavailable = Install::isGdAvailable();

// Handle postbacks for form
if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
	// Form was posted back
	$databasehost = filter_input(INPUT_POST, 'databasehost');
	$databaseusername = filter_input(INPUT_POST, 'databaseusername');
	$databasepassword = filter_input(INPUT_POST, 'databasepassword');
	$databasename = filter_input(INPUT_POST, 'databasename');
	$tableprefix = filter_input(INPUT_POST, 'tableprefix');
	$defaultlanguage = filter_input(INPUT_POST, 'defaultlanguage');
	$GLOBALS['databasehost'] = $databasehost;
	$GLOBALS['databaseusername'] = $databaseusername;
	$GLOBALS['databasepassword'] = $databasepassword;
	$GLOBALS['databasename'] = $databasename;
	$GLOBALS['tableprefix'] = $tableprefix;
	$GLOBALS['defaultlanguage'] = $defaultlanguage;
	// Check database connection
	$databaseerror = !Install::canAccessDatabase();
	if ($candeleteinstallsript && $canwritelocalconfig && $canwritemediadir && $canwritelocaledir && $isdatabaseavailable && $isgdavailable && !$databaseerror) {
		// Store localconfig file
		$installationprogress = Install::createLocalConfig();
		// Perform the database installation
		$installationprogress .= Install::createAndUpdateTables($tableprefix);
		rename('install.php', 'install.php.bak');
		$installationprogress .= '<p class="success">'.sprintf(__('The installation is completed. The %s file was renamed to %s for security reasons.'), 'install.php', 'install.php.bak').'</p>';
	} else {
		$installationprogress = false;
	}
} else {
	// Single GET page call
	if (file_exists('config/localconfig.inc.php')) {
		require_once 'config/localconfig.inc.php';
	} else {
		$databasehost = 'localhost';
		$databaseusername = '';
		$databasepassword = '';
		$databasename = '';
		$tableprefix = 'mps_';
		$defaultlanguage = 'en';
	}
	$databaseerror = null;
	$installationprogress = false;
}

?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo __('Install MyPhotoStorage') ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
		<link rel="stylesheet" href="static/css/install.css" />
	</head>
	<body>
		<h1><?php echo __('Install MyPhotoStorage') ?></h1>
		<form method="post" action="install.php#end" class="simple install">
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
				<?php if ($canwritelocaledir) : ?>
				<p class="success"><?php echo __('Locale directory is writeable.') ?></p>
				<?php else : ?>
				<p class="error"><?php echo sprintf(__('Locale directory is not writeable. Please make sure that the webserver process has write permissions to the directory %s'), Install::$localeDir) ?></p>
				<?php endif ?>
			</div>
			<h2><?php echo __('PHP modules') ?></h2>
			<div>
				<?php if ($isdatabaseavailable) : ?>
				<p class="success"><?php echo __('Database is available.') ?></p>
				<?php else : ?>
				<p class="error"><?php echo __('The Database PHP extension is not available. Please install it. On Debian you can use "sudo apt-get install php5-mysql"') ?></p>
				<?php endif ?>
				<?php if ($isgdavailable) : ?>
				<p class="success"><?php echo __('GD is available.') ?></p>
				<?php else : ?>
				<p class="error"><?php echo __('The GD PHP extension is not available. Please install it. On Debian you can use "sudo apt-get install php5-gd"') ?></p>
				<?php endif ?>
			</div>
			<h2><?php echo __('Database connection') ?></h2>
			<div>
				<?php if ($isdatabaseavailable) : ?>
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
				<?php endif ?>
				<?php if ($databaseerror === true) : ?>
				<p class="error"><?php echo __('Cannot access database. Please check the settings above.') ?></p>
				<?php elseif ($databaseerror === false) : ?>
				<p class="success"><?php echo __('Database connection succeeded.') ?></p>
				<?php endif ?>
			</div>
			<h2><?php echo __('Language') ?></h2>
			<div>
				<label><?php echo __('Default language') ?></label>
				<input type="text" name="defaultlanguage" value="<?php echo $defaultlanguage ?>" />
			</div>
			<div>
				<input type="submit" value="<?php echo __('Install') ?>" />
			</div>
			<?php if ($installationprogress) : ?>
			<h2><?php echo __('Installation progress') ?></h2>
			<div>
				<?php echo $installationprogress ?>
			</div>
			<div><a href="account/register.php"><?php echo __('Register a new account') ?></a></div>
			<?php endif ?>
		</form>
		<a name="end"></a>
	</body>
</html>