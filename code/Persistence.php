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
	 * @var resource Connection to MySQL database. Initialized when the
	 * first call to any function is done.
	 */
	private static $connection;
	
	/**
	 * Returns or initializes the connection from the settings of the localconfig.inc.php.
	 * This file must be included before accessing the persistence framework.
	 * 
	 * @return coonnection Returns the inisitalized database connection.
	 */
	static function getConnection() {
		if (!self::$connection) {
			$databasehost = $GLOBALS['databasehost'];
			$databaseusername = $GLOBALS['databaseusername'];
			$databasepassword = $GLOBALS['databasepassword'];
			$databasename = $GLOBALS['databasename'];
			self::$connection = mysqli_connect($databasehost, $databaseusername, $databasepassword, $databasename);
		}
		return self::$connection;
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
		$existingtables = self::query('show tables like \''.$tableprefix.$tablename.'\'');
		if (count($existingtables) > 0) {
			return false;
		}
		$createquery = 'create table '.$tableprefix.$tablename.' ('.$tableprefix.$tablename.'_id bigint unsigned not null auto_increment primary key) engine = INNODB';
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
		$existingcolumns = self::query('show columns from '.$tableprefix.$tablename.' like \''.$tableprefix.$columnname.'\'');
		if (count($existingcolumns) > 0) {
			return false;
		}
		if (is_array($columndefinition)) {
			// When this is an array, we have a reference to another table
			$sqldefinition = $columndefinition[0].', add foreign key fk_'.$tableprefix.$columnname.'('.$tableprefix.$columnname.') references '.$tableprefix.$columndefinition[1].'('.$tableprefix.$columndefinition[1].'_id) on delete cascade on update cascade';
		} else {
			$sqldefinition = $columndefinition;
		}
		$createquery = 'alter table '.$tableprefix.$tablename.' add column '.$tableprefix.$columnname.' '.$sqldefinition;
		self::query($createquery);
		return true;
	}
	
	/**
	 * Executes the given query and returns an array of associated arrays
	 * for the result.
	 * 
	 * @param string $query Query to perform
	 * @return array Array of rows of the result or nothing when the  query has no result.
	 * @throws Exception When the query contains an error.
	 */
	static function query($query) {
		$connection = self::getConnection();
		$result = $connection->query($query);
		if ($result) {
			if ($result !== TRUE) {
				$array = [];
				while ($row = $result->fetch_assoc()) {
					$array[] = $row;
				}
				return $array;
			} else if (strtolower(substr($query, 0, 6)) === 'insert') {
				return $connection->insert_id;
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
		return mysqli_real_escape_string(self::getConnection(), $value);
	}
}