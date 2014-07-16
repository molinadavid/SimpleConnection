<?php
/**
 * This is the class that controls all the queries on the DB for
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
 * @package       SimpleConnection
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

require_once __DIR__.'/database/connections.php';
/***************************************
Query type:
* 0 = nothing
* 1 = select
* 2 = update
* 3 = insert
* 4 = free fetch


Select type
* 0 = nothing
* 1 = select all
* 2 = select single

***************************************/


class SimpleConnection{

	protected $safe_operators = array('BETWEEN', 'NOT BETWEEN', '>', '>=', '=', '<=', '<', 'IS NOT', 'IS', 'LIKE', 'NOT LIKE', '!=', '<>');

	protected $query_type = 0;
	protected $select_type = 0;
	protected $query_values = array();
	
	protected $db_table;
	protected $t_value;
	protected $t_select;
	protected $t_order;
	protected $t_set;
	protected $t_insert;
	
	protected $t_count = 0;

	/**
	*	@param string the name of the table to be used
	*/
	public function __construct($table = '') {
		if ($table !== '') {
			$this->db_table = $table;
		}
	}
	
	/**
	*	Function to provide a free query option
	*	letting you use any givven query that may not be supported by
	*	the library.
	*
	*	@param string $query, The query to be executed.
	*	@param array $values, The values for the provided query.
	*	@return array, The result from running the query.
	*/
	public function freeQuery($query, $values){
		if($this->query_type != 4){$this->query_type = 4;}
		$this->t_value = $query;
		$this->query_values = $values;

		return $this->run();
	}

	/**
	*	Function that handles the selection part of the queries.
	*
	*	@param string/array $fields, The fields to be selected (default 'all').
	*	@param string $type, The amount of rows to fetch (default 'all').
	*/
	public function select($fields = 'all', $type = 'all'){
		if(strtolower($type) !== 'all' && strtolower($type) !== 'single'){
			throw new Exception('Select type invalid('.$type.') it has to be all or single');
		}
		if(strtolower($type) == 'single'){
			if($this->select_type != 2){$this->select_type = 2;}
		}else{
			if($this->select_type != 1){$this->select_type = 1;}
		}
		if(!is_array($fields) && strtolower($fields) === 'all'){$fields = '*';}

		if($this->query_type != 1){$this->query_type = 1;}

		if(is_array($fields)){
			$t_s = "";
			foreach($fields as $value){
				if($value && $this->validateValues($value)){
					$t_s = (strlen($t_s)>0 ? $t_s.",".$value : $value);
				}
			}
			$this->t_select = $t_s;
		}else if($this->validateValues($fields)){
			$this->t_select = $fields;
		}

		
	}
	
	/**
	*	Function that handles the update part of the queries.
	*
	*	@param array $array, Associative array with the fields and values to update.
	*/
	public function update($array){
		if(is_array($array)){
			$temp_string = "";
			foreach($array as $key=>$value){
				$temp_key = ":".$key."_".$this->t_count;
				$temp_string .= (strlen($temp_string) > 0 ? ",".$key."=".$temp_key : $key."=".$temp_key);
				
				$temp_array = array($temp_key=>$value);
				array_push($this->query_values, $temp_array);
				
				$this->t_count ++;
			}
			$this->t_set = $temp_string;
		}
	}
	
	/**
	*	Function that handles the insert part of the queries.
	*
	*	@param array $array, Associative array with the fields and values to insert.
	*	@return array, The result from running the query.
	*/
	public function insert($array){
		if(is_array($array)){
			if($this->query_type != 3){$this->query_type = 3;}
			
			$temp_ins = "";
			$temp_val = "";
			foreach($array as $key=>$value){
				$temp_key = ":".$key."_".$this->t_count;
				$temp_ins .= (strlen($temp_ins)>0 ? ",".$key : $key);
				$temp_val .= (strlen($temp_val)>0 ? ",".$temp_key : $temp_key);
				
				$temp_array = array($temp_key=>$value);
				array_push($this->query_values, $temp_array);
				
				$this->t_count ++;
			}
			
			$this->t_insert = $temp_ins;
			$this->t_value = $temp_val;

			return $this->run();
		}
	}
	
	/**
	*	Function that handles the query comparators.
	*
	*	@param array $values, array with the fields and values to compare, array('key', 'operator', 'value').
	*	@param array of arrays $values, array(array('key', 'operator', 'value', 'next'), array('key', 'operator', 'value')).
	*/
	public function where($values){
		
		if (!is_array($values)) {
			throw new Exception('The values to query must come on an array format.');
		}

		if (is_array($values[0])) {
			$temp_string = '';
			foreach ($values as $value) {
				if (count($value) > 4 || count($value) < 3) {
					throw new Exception('Please verify the correct format for the AND function.');
				}
				
				$temp_string .= ' '.$value[0].$value[1].':'.$value[0].'_'.$this->t_count;
				$temp_key = ":".$value[0]."_".$this->t_count;
				$temp_array = array($temp_key=>$value[2]);
				array_push($this->query_values, $temp_array);
				$this->t_count ++;
				
				if (count($value) == 4) {
					$temp_string .= ' '.$value[3];
				}else{
					break;
				}
				
			}

			$this->t_value .= ' ('.$temp_string.') ';

		}else{
			if (count($values) > 3 || count($values) < 2) {
				throw new Exception('Please verify the correct format for the AND function.');
			}

			$temp_string = $values[0].$values[1].":".$values[0]."_".$this->t_count;
			$temp_key = ":".$values[0]."_".$this->t_count;
			if (count($values) == 3) {
				$temp_array = array($temp_key=>$values[2]);
			}else{
				$temp_array = array($temp_key=>"''");
			}
			array_push($this->query_values, $temp_array);
			
			$this->t_value .= $temp_string;
			$this->t_count ++;
		}
		
	}

	/**
	*	Function that handles the next comparator with AND value
	*
	*	@param array $values, array with the fields and values to compare, array('key', 'operator', 'value').
	*	@param array of arrays $values, array(array('key', 'operator', 'value', 'next'), array('key', 'operator', 'value')).
	*/
	public function sc_and($values) {
		if (!is_array($values)) {
			throw new Exception('The values to query must come on an array format.');
		}

		if (is_array($values[0])) {
			$t_value = '';
			foreach ($values as $value) {
				if (count($value) > 4 || count($value) < 3) {
					throw new Exception('Please verify the correct format for the AND function.');
				}

				$t_value .= ' '.$value[0].$value[1].':'.$value[0].'_'.$this->t_count;
				$temp_key = ":".$value[0]."_".$this->t_count;
				$temp_array = array($temp_key=>$value[2]);
				array_push($this->query_values, $temp_array);
				$this->t_count ++;

				if (count($value) == 4) {
					$t_value .= ' '.$value[3];
				}else{
					break;
				}
			}

			$this->t_value .= ' AND ('.$t_value.') ';

		}else{
			if (count($values) > 3 || count($values) < 2) {
				throw new Exception('Please verify the correct format for the AND function.');
			}

			$temp_string = $values[0].$values[1].":".$values[0]."_".$this->t_count;
			$temp_key = ":".$values[0]."_".$this->t_count;
			if (count($values) == 3) {
				$temp_array = array($temp_key=>$values[2]);
			}else{
				$temp_array = array($temp_key=>"''");
			}
			array_push($this->query_values, $temp_array);
			
			$this->t_value .= ' AND '.$temp_string;
			$this->t_count ++;
		}
	}

	/**
	*	Function that handles the next comparator with OR value
	*
	*	@param array $values, array with the fields and values to compare, array('key', 'operator', 'value').
	*	@param array of arrays $values, array(array('key', 'operator', 'value', 'next'), array('key', 'operator', 'value')).
	*/
	public function sc_or($values) {
		if (!is_array($values)) {
			throw new Exception('The values to query must come on an array format.');
		}

		if (is_array($values[0])) {
			$t_value = '';
			foreach ($values as $value) {
				if (count($value) > 4 || count($value) < 3) {
					throw new Exception('Please verify the correct format for the AND function.');
				}

				$t_value .= ' '.$value[0].$value[1].':'.$value[0].'_'.$this->t_count;
				$temp_key = ":".$value[0]."_".$this->t_count;
				$temp_array = array($temp_key=>$value[2]);
				array_push($this->query_values, $temp_array);
				$this->t_count ++;

				if (count($value) == 4) {
					$t_value .= ' '.$value[3];
				}else{
					break;
				}
			}

			$this->t_value .= ' OR ('.$t_value.') ';

		}else{
			if (count($values) > 3 || count($values) < 2) {
				throw new Exception('Please verify the correct format for the AND function.');
			}

			$temp_string = $values[0].$values[1].":".$values[0]."_".$this->t_count;
			$temp_key = ":".$values[0]."_".$this->t_count;
			if (count($values) == 3) {
				$temp_array = array($temp_key=>$values[2]);
			}else{
				$temp_array = array($temp_key=>"''");
			}
			array_push($this->query_values, $temp_array);
			
			$this->t_value .= ' OR '.$temp_string;
			$this->t_count ++;
		}
	}

	/**
	*	Optional function to set the order of the query
	*
	*	@param string $order, String with the order by values.
	*/
	function set_order($order){
		$this->t_order = $order;
	}
	
	/**
	*	Function that executes the query if is not automatically executed.
	*
	*	@return array, The result from running the query.
	*/
	function run(){
	
		$values = array();
		if($this->query_type != 4){
			foreach($this->query_values as $first){foreach($first as $key=>$value){$values[$key] = $value;}}
		}

		switch($this->query_type){
			case 1:
				$query = "	SELECT
								".$this->t_select."
							FROM
								".$this->db_table."
							WHERE
								".$this->t_value." ";
				
				if($this->t_order){$query .= $this->t_order;}
				
				if($this->select_type == 1){
					$sc_object = new SC_DBFunctions($query, $values);
					return $sc_object->sc_select('all');
				}elseif($this->select_type == 2){
					$sc_object = new SC_DBFunctions($query, $values);
					return $sc_object->sc_select('single');
				}
			break;
			
			case 2:
				$query = "	UPDATE
								".$this->db_table."
							SET
								".$this->t_set."
							WHERE
								".$this->t_value." ";

				$sc_update = new SC_DBFunctions($query, $values);
				return $sc_update->update();
			break;
			
			case 3:
				$query = "	INSERT INTO
								".$this->db_table."
								(".$this->t_insert.")
							VALUES
								(".$this->t_value.") ";

				$sc_insert = new SC_DBFunctions($query, $values);
				return $sc_insert->insert();
			break;
			
			case 4:
				$_query = $this->t_value;
				$_values = $this->query_values;

				$sc_free = new SC_DBFunctions($_query, $_values);
				return $sc_free->free_fetch();
			break;
		}
	}

/***************************************
		Validation functions
***************************************/
	function validateValues($v){
		$toReturn = false;
		if(strlen($v) > 0){$toReturn = true;}
		return $toReturn;
	}
	
	function validateOperator($o){
		$toReturn = false;
		foreach($this->safe_operators as $value){if($o && strcasecmp($value, $o) == 0){$toReturn = true;}}
		return $toReturn;
	}
	
	
}

