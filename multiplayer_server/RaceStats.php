<?php

class RaceStats {

	public $temp_id;
	public $name;
	public $rank;
	public $ip;
	public $finish_time;
	public $drawing = true;
	public $still_here = true;

	public $level_hash = '';
	public $mode = '';
	public $finish_positions = '';
	public $finish_count = '';
	public $cowboy_chance = '';

	public $eggs = 0;
	public $objectives_reached = array();
	public $last_objective_time = 0;

	public function __construct($temp_id, $name, $rank, $ip){
		$this->temp_id = $temp_id;
		$this->name = $name;
		$this->rank = $rank;
		$this->ip = $ip;
	}
}

?>
