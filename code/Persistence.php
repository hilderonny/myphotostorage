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
 * Handles database connections and queries
 *
 * @author Ronny Hildebrandt <ronny.hildebrandt@avorium.de>
 */
class Persistence {

	/**
	 * @var resource Connection to PostgreSQL database. Initialized when the
	 * first call to any function is done.
	 */
	static $connection;
	
	/**
	 * Initializes the connection from the settings of the localconfig.inc.php.
	 * This file must be included before accessing the persistence framework.
	 */
	static function init() {
		if (self::$connection) {
			return;
		}
		$databasehost = $GLOBALS['databasehost'];
		$databaseusername = $GLOBALS['databaseusername'];
		$databasepassword = $GLOBALS['databasepassword'];
		$databasename = $GLOBALS['databasename'];
		self::$connection = pg_pconnect("host=$databasehost dbname=$databasename user=$databaseusername password=$databasepassword");
	}

	/**
	 * Inserts the given data into the given table.
	 * 
	 * @param string $tablename Table to insert the data into
	 * @param array $data Associative array containing the column names as keys
	 * and the values to insert as values. All Values must be strings.
	 */
	static function insert($tablename, $data) {
		self::init();
		$tableprefix = $GLOBALS['tableprefix'];
		$values = array_values($data);
		$escapedvalues = [];
		foreach ($values as $value) {
			$escapedvalues[] = pg_escape_string(self::$connection, $value);
		}
		$query = 'insert into '.$tableprefix.$tablename.' ('.implode(',', array_keys($data)).') values (\''.implode('\',\'', $escapedvalues).'\')';
		self::query($query);
	}
	
	/**
	 * Updates the given data in the given table for records with the given
	 * id.
	 * 
	 * @param string $tablename Table to update its data
	 * @param array $data Associative array containing the column names as keys
	 * and the values to insert as values. All Values must be strings.
	 * @param string $id Id of the record to update
	 */
	static function update($tablename, $data, $id) {
		self::init();
		$tableprefix = $GLOBALS['tableprefix'];
		$setvalues = [];
		foreach ($data as $key => $value) {
			$setvalues[] = $key.'="'.pg_escape_string(self::$connection, $value).'"';
		}
		$query = 'update '.$tableprefix.$tablename.' set '.implode(',', $setvalues).' where '.$tableprefix.$tablename.'_id='.$id;
		self::query($query);
	}
	
	/**
	 * Creates a table with the given name and inserts a column with name
	 * TABLENAME_id as long autoincrement primary key. When a table with the
	 * same name exists, nothing will be done here.
	 * 
	 * @param string $tablename Name of the table to create.
	 * @return boolean True when table was created, false when table exists.
	 */
	static function createTable($tablename) {
		$tableprefix = $GLOBALS['tableprefix'];
		$existingtables = self::query('select table_name from information_schema.tables where table_schema=\'public\' and table_name=\''.$tableprefix.$tablename.'\'');
		if (count($existingtables) > 0) {
			return false;
		}
		$createquery = 'create table '.$tableprefix.$tablename.' ('.$tablename.'_id bigserial primary key)';
		self::query($createquery);
		return true;
	}
	
	static function query($query) {
		self::init();
		$result = pg_query(self::$connection, $query);
		if ($result) {
			if ($result !== TRUE) {
				$array = [];
				while ($row = pg_fetch_assoc($result)) {
					$array[] = $row;
				}
				return $array;
			}
		} else {
			throw new Exception('Error in query: '.$query);
		}
	}
}