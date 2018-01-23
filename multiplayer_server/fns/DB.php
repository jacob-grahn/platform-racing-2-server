<?php

class DB {

	private $mysqli;
	private $last_action;

	public function __construct ($conn_name='') {
		if($conn_name == '') {
			$this->mysqli = user_connect();
			$this->last_action = time();
		}
		else {
			throw new Exception('DB::__construct - unknown connection name');
		}
	}

	public function real_escape_string ($val) {
		return $this->mysqli->real_escape_string($val);
	}

	public function escape ($val) {
		return $this->mysqli->real_escape_string($val);
	}

	public function get_insert_id () {
		return($this->mysqli->insert_id);
	}

	public function get_error () {
		return($this->mysqli->error);
	}

	public function test_health () {
		$elapsed = time() - $this->last_action;
		if($elapsed > 3) {
			$connected = $this->mysqli->ping();
			if(!$connected) {
				$this->mysqli->close();
				$this->mysqli = user_connect();
				$this->last_action = time();
			}
		}
	}

	public function query ($query_str) {
		$this->test_health();

		while ($this->mysqli->more_results()) {
			$this->mysqli->next_result();
		}

		$start_time = microtime(true);

		$result = $this->mysqli->query($query_str);
		if(!$result) {
			throw new Exception('Could not perform query.');
		}

		$end_time = microtime(true);
		$elapsed_time = $end_time - $start_time;

		$this->last_action = time();

		return $result;
	}

	public function next_result () {
		return $this->mysqli->next_result();
	}

	public function format_call ($func, $arg_list=NULL) {
		if(!isset($arg_list)) {
			$arg_list = array();
		}

		if(!is_string($func)) {
			throw new Exception('DB::call - Invalid call func');
		}

		$arg_count = count($arg_list);
		$safe_func = $this->escape($func);

		$query_str = 'CALL '.$safe_func.'(';

		for($i=0; $i<$arg_count; $i++) {
			$arg = $arg_list[$i];
			$safe_arg = $this->escape($arg);
			$query_str .= '"'.$safe_arg.'"';
			if($i<$arg_count-1) {
				$query_str .= ', ';
			}
		}

		$query_str .= ');';

		return $query_str;
	}

	public function call ($func, $arg_list=NULL, $resultmode = MYSQLI_STORE_RESULT) {
		if(!isset($arg_list)) {
			$arg_list = array();
		}
		$query_str = $this->format_call($func, $arg_list);
		$result = $this->query($query_str, $func, $resultmode);
		return $result;
	}

	public function grab_row ($func, $arg_list=NULL) {
		$result = $this->call($func, $arg_list);
		if($result->num_rows != 1) {
			$row = null;
			throw new Exception('Found '. $result->num_rows .' rows for DB->grab_row '.$func);
		}
		else {
			$row = $result->fetch_object();
		}
		return($row);
	}

	public function grab ($var, $func, $arg_list=NULL) {
		$row = $this->grab_row($func, $arg_list);
		if(isset($row)) {
			$val = $row->{$var};
		}
		return($val);
	}

	public function to_array ( $result ) {
		$arr = array();
		while( $row = $result->fetch_object() ) {
			$arr[] = $row;
		}
		return $arr;
	}
}

?>
