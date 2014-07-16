<?php
/**
 * This is the class that exposes the conections to MYSQL DB for
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

//TODO: return SC_Exception on failure

require_once __DIR__.'/dbconnections.php';

class SC_DBFunctions extends SC_DBConnection {
	protected $_SC_query;
	protected $_SC_values;

	public function __construct($query, $values) {
		$this->_SC_query = $query;
		$this->_SC_values = $values;
		parent::__construct();
	}

	public function free_fetch() {
		$connection = $this->connect();
		if($connection){
			try{
				$connection->beginTransaction();
			
				$stmt = $connection->prepare($this->_SC_query);
				$stmt->execute($this->_SC_values);
				$result = $stmt->fetchAll();
				$stmt->closeCursor();
				$connection->commit();
				
				return $result;
			
			}catch(PDOException $e){
				$connection->rollBack();
				return $e->getMessage();
			}
		}else{
			echo false;
		}
	}

	public function sc_select($type = 'all') {
		
		if ($type != 'all' && $type != 'single') {
			throw new Exception('Select type invalid('.$type.') it has to be all or single');
		}

		$connection = $this->connect();
		if($connection){
			try{
				$connection->beginTransaction();
			
				$stmt = $connection->prepare($this->_SC_query);
				$stmt->execute($this->_SC_values);
				if ($type == 'all') {
					$result = $stmt->fetchAll();
				}else{
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
				}
				$stmt->closeCursor();
				$connection->commit();
				
				return $result;
			
			}catch(PDOException $e){
				$connection->rollBack();
				return $e->getMessage();
			}
		}else{
			echo false;
		}
	}

	public function update() {
		$connection = $this->connect();
		if($connection){
			try{
				$connection->beginTransaction();		
				$stmt = $connection->prepare($this->_SC_query);
				$stmt->execute($this->_SC_values);
				$connection->commit();
				
				return true;
			}catch(PDOEXception $e){
				$connection->rollback();
				return $e->getMessage();
			}
		}else{
			return false;
		}
	}

	public function insert() {
		$connection = $this->connect();
		if($connection){
			try{
				$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES,TRUE);
				$connection->beginTransaction();
				$stmt = $connection->prepare($this->_SC_query);
				$stmt->execute($this->_SC_values);
				$id = $connection->lastInsertId();
				$connection->commit();
				
				return $id;
			}catch(PDOException $e){
				$connection->rollback();
				return $e->getMessage();
			}
		}else{
			echo false;
		}
	}

}
