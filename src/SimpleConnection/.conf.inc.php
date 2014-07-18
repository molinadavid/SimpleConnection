<?php
/**
 * This is the basic configuration file for
 * Simple connection PDO library
 *
 * PHP 5
 *
 * Copyright 2014, David Molina.
 * Developed by David Molina.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2014, David Molina.
 * @link          molinadavid@hotmail.co.uk
 * @package       SimpleConnection
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * In this file you set up your database connection details.
 *
 * @package       SimpleConnection
 */
/**
 * Database configuration class.
 * TODO: You can specify multiple configurations for production, development and testing.
 *
 * datasource => The name of a supported datasource; valid options are as follows:
 *		Database/Mysql 		- MySQL 4 & 5
 *
 *
 *
 * persistent => true / false
 * Determines whether or not the database should use a persistent connection
 *
 * host =>
 * the host you connect to the database. To add a socket or port number, use 'port' => #
 *
 * prefix =>
 * Uses the given prefix for all the tables in this database.  This setting can be overridden
 * on a per-table basis with the Model::$tablePrefix property.
 *
 * schema =>
 * For Postgres specifies which schema you would like to use the tables in. Postgres defaults to 'public'.
 *
 * encoding =>
 * For MySQL, Postgres specifies the character encoding to use when connecting to the
 * database. Uses database default not specified.
 *
 * unix_socket =>
 * For MySQL to connect via socket specify the `unix_socket` parameter instead of `host` and `port`
 */

class SC_CONFIG {
	
	public $default = array(
    	'datasource' => 'Database/Mysql',
    	'persistent' => false,
    	'host' => 'localhost',
    	'port' => '',
    	'login' => '<Your user name>',
    	'password' => '<Your password>',
    	'database' => '<Your DB>',
    	'schema' => '',
    	'prefix' => '',
    	'encoding' => 'utf8'
	);

	public $test = array(
    	'datasource' => 'Database/Mysql',
    	'persistent' => false,
    	'host' => 'localhost',
    	'port' => '',
    	'login' => '<Your user name>',
        'password' => '<Your password>',
        'database' => '<Your DB>',
    	'schema' => '',
    	'prefix' => '',
    	'encoding' => 'utf8'
	);
}

/**
* Global declarations for date formats
*/
define("SC_TIMEX", time());
define("SC_TODAY", date('Y-m-d H:i:s'));
define("SC_TYEAR", date('Y'));
define("SC_TMONTH", date('m'));
define("SC_TDAY", date('d'));
define("SC_THOUR", date('H'));
define("SC_TMINUTE", date('i'));



