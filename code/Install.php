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
 * Class with helper functions for installing and updating the 
 * MyPhotoStorage installation.
 *
 * @author Ronny Hildebrandt <ronny.hildebrandt@avorium.de>
 */
class Install {
	
	/**
	 * @var string Path of the install.php file
	 */
	static $installFileName = './install.php';
	
	/**
	 * @var string Path of the localconfig.inc.php file
	 */
	static $localConfigFileName = './config/localconfig.inc.php';
	
	/**
	 * @var string Path of the media directory
	 */
	static $mediaDir = './data/media/';
	
	/**
	 * @var string Path of the locale directory
	 */
	static $localeDir = './locale/';
	
	/**
	 * Tests whether the system can write to the file 
	 * config/localconfig.inc.php and returns true on success.
	 * 
	 * @return boolean True when the config file can be written, false otherwise
	 */
	static function canWriteLocalConfig() {
		$canwrite = is_writable(self::$localConfigFileName);
		return $canwrite;
	}
	
	/**
	 * Tests whether the system can delete the file 
	 * install.php and returns true on success.
	 * 
	 * @return boolean True when the install.php file can be written, 
	 * false otherwise
	 */
	static function canDeleteInstallScript() {
		return is_writable(self::$installFileName);
	}
	
	/**
	 * Tests whether the system can write into the media directory
	 * and returns true on success.
	 * 
	 * @return boolean True when the media directory can be written, 
	 * false otherwise
	 */
	static function canWriteMediaDir() {
		return is_writable(self::$mediaDir);
	}
	
	/**
	 * Tests whether the system can write into the locale directory
	 * and returns true on success.
	 * 
	 * @return boolean True when the locale directory can be written, 
	 * false otherwise
	 */
	static function canWriteLocaleDir() {
		return is_writable(self::$localeDir);
	}
	
	/**
	 * Testst whether the PHP PostgreSQL extension is available.
	 * 
	 * @return boolean True when the PHP PostgreSQL extension is loaded.
	 */
	static function isPostgresAvailable() {
		return extension_loaded('pgsql');
	}
	
	/**
	 * Tests whether the system can access the database and returns 
	 * true on success.
	 * 
	 * @return boolean True when the database can be accessed, false otherwise
	 */
	static function canAccessDatabase() {
		// pg_connect produces a warning which is okay here. So we
		// prevent PHP from writing out the warning in this case
		set_error_handler(function() { /* ignore errors */ });
		$connectionexists = Persistence::getConnection() !== false;
		restore_error_handler();
		return $connectionexists;
	}

	/**
	 * Creates the localconfig.inc.php file with the settings from
	 * the GLOBAL variables.
	 */
	static function createLocalConfig() {
		$content = '<?php'."\n"
			.'$GLOBALS[\'databasehost\'] = \''.$GLOBALS['databasehost'].'\';'."\n"
			.'$GLOBALS[\'databaseusername\'] = \''.$GLOBALS['databaseusername'].'\';'."\n"
			.'$GLOBALS[\'databasepassword\'] = \''.$GLOBALS['databasepassword'].'\';'."\n"
			.'$GLOBALS[\'databasename\'] = \''.$GLOBALS['databasename'].'\';'."\n"
			.'$GLOBALS[\'tableprefix\'] = \''.$GLOBALS['tableprefix'].'\';'."\n"
			.'$GLOBALS[\'defaultlanguage\'] = \''.$GLOBALS['defaultlanguage'].'\';'."\n";
		file_put_contents(self::$localConfigFileName, $content, LOCK_EX);
		return '<p>'.sprintf(__('Created localconfig.inc.php with content <pre>%s</pre>'), htmlentities($content)).'</p>';
	}
	
	/**
	 * Creates a table (when not existing) and appends missing columns.
	 * 
	 * @param string $tablename Name of the table to create / extend
	 * @param array $columns Array of column definitions with column names
	 * as keys and column definitions as values in PostgreSQL format.
	 * @return string Status message of the process for outputting in
	 * install script.
	 */
	static function createAndUpdateTable($tablename, $columns = []) {
		$result = '';
		// (Try to) Create table
		if (Persistence::createTable($tablename)) {
			$result .= '<p>'.sprintf(__('Created table %s.'), $tablename).'</p>';
		}
		foreach ($columns as $columnname => $columndefinition) {
			// Add column if not existent
			if (Persistence::createColumn($tablename, $columnname, $columndefinition)) {
				$result .= '<p>'.sprintf(__('Created column %s.%s.'), $tablename, $columnname).'</p>';
			}
		}
		return $result;
	}
	
	/**
	 * Creates alle needed database tables and appends new columns if neccessary
	 */
	static function createAndUpdateTables() {
		$result = '';
		$result .= self::createAndUpdateTable('users', ['users_username' => 'text', 'users_password' => 'text', 'users_email' => 'text']);
		$result .= self::createAndUpdateTable('media', ['media_filename' => 'text', 'media_mimetype' => 'text', 'media_location' => 'text', 'media_owner_users_id' => 'bigint references users(users_id) on delete cascade', 'media_status' => 'text']);
		$result .= self::createAndUpdateTable('albums', ['albums_owner_users_id' => 'bigint references users(users_id) on delete cascade', 'albums_name' => 'text', 'albums_status' => 'text']);
		$result .= self::createAndUpdateTable('albummedia', ['albummedia_albums_id' => 'bigint references albums(albums_id) on delete cascade', 'albummedia_media_id' => 'bigint references media(media_id) on delete cascade']);
		$result .= self::createAndUpdateTable('mediacrons', ['mediacrons_media_id' => 'bigint references media(media_id) on delete cascade', 'mediacrons_action' => 'text']);
		return $result;
	}
}
