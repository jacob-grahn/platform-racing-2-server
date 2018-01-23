<?php

class LevelListRoom extends Room {
	
	public $course_array = array();
	protected $room_name = 'right_room';
	
	
	public function __construct() {
		LoiterDetector::add_level_list($this);
	}
	
	
	public function add_player($player){
		Room::add_player($player);
		foreach($this->course_array as $course){
			$course->catch_up($player);
		}
	}
	
	
	public function remove_player($player){
		if(isset($player->course_id)){
			$course = $this->course_array[$player->course_id];
			$course->clear_slot($player);
		}
		Room::remove_player($player);
	}
	
	
	public function fill_slot($player, $course_id, $slot){
		if(!is_numeric($slot) || $slot < 0 || $slot > 3) {
			$slot = 0;
		}
		if(isset($this->course_array[$course_id])){
			$this->course_array[$course_id]->fill_slot($player, $slot);
		}else{
			$course = new CourseBox($course_id, $this);
			$course->fill_slot($player, $slot);
		}
	}
}