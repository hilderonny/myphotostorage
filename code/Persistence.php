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
			$escapedvalues[] = self::escape($value);
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
			$setvalues[] = $key.'=\''.self::escape($value).'\'';
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
	
	/**
	 * Creates a column in a table with the given name when it does not exist.
	 * 
	 * @param string $tablename Name of the table to create a column for.
	 * @param string $columnname Name of the column to create.
	 * @param string $columndefinition Definition of the column in PostgreSQL format.
	 * @return boolean True when column was created, false when column exists.
	 */
	static function createColumn($tablename, $columnname, $columndefinition) {
		$tableprefix = $GLOBALS['tableprefix'];
		$existingcolumns = self::query('select column_name from information_schema.columns where table_schema=\'public\' and  table_name = \''.$tableprefix.$tablename.'\' and column_name=\''.$columnname.'\'');
		if (count($existingcolumns) > 0) {
			return false;
		}
		$createquery = 'alter table '.$tableprefix.$tablename.' add column '.$columnname.' '.$columndefinition;
		self::query($createquery);
		return true;
	}
	
	/**
	 * Executes the given query and returns an array of associated arrays
	 * for the result.
	 * 
	 * @param string $query Query to perform
	 * @return array Array of rows of the result or nothing when the  query has no result.
	 * @throws Exception Qhen the query contains an error.
	 */
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
	
	/**
	 * Returns the escaped string for the given value, safe to insert into the database.
	 * 
	 * @param string $value Value to escape
	 * @return string Escaped value
	 */
	static function escape($value) {
		self::init();
		return pg_escape_string(self::$connection, $value);
	}
}