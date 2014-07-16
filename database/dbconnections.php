<?php
/**
 * This is the class that controlls the conections to MYSQL DB for
 * Simple connection PDO library
 *
 * PHP 5
 *
 * Copyright 2014, Seaos Corp.
 * Developed by David Molina.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2014, Seaos Corp.
 * @link          http://seaos.co.jp
 * @package       SimpleConnection.database
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

require_once __DIR__.'/../.conf.inc.php';

class SC_DBConnection {
	protected $_config;

	public function __construct() {
		$conf = new SC_CONFIG();
		$this->_config = $conf->default;
	}

	public function connect(){
		$connected = false;

		$flags = array(
			PDO::ATTR_PERSISTENT => $this->_config['persistent'],
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		
		if (!empty($this->_config['encoding'])) {
			$flags[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->_config['encoding'];
		}
		if (!empty($this->_config['ssl_key']) && !empty($this->_config['ssl_cert'])) {
			$flags[PDO::MYSQL_ATTR_SSL_KEY] = $this->_config['ssl_key'];
			$flags[PDO::MYSQL_ATTR_SSL_CERT] = $this->_config['ssl_cert'];
		}
		if (!empty($this->_config['ssl_ca'])) {
			$flags[PDO::MYSQL_ATTR_SSL_CA] = $this->_config['ssl_ca'];
		}
		if (empty($this->_config['unix_socket'])) {
			$dsn = "mysql:host={$this->_config['host']};dbname={$this->_config['database']}";
		} else {
			$dsn = "mysql:unix_socket={$this->_config['unix_socket']};dbname={$this->_config['database']}";
		}
		
		try {
			return new PDO($dsn, $this->_config['login'], $this->_config['password'], $flags);
			$connected = true;
			
		} catch (PDOException $e) {
			echo 'ERROR: '.$e->getMessage();
			return false;
		}
		date_default_timezone_set('Asia/Tokyo');
	}
}
	
