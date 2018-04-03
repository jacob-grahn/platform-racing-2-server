<?php

class LevelListRoom extends Room
{
    
    public $course_array = array();
    protected $room_name = 'right_room';
    
    
    public function __construct()
    {
        LoiterDetector::add_level_list($this);
    }
    
    
    public function add_player($player)
    {
        Room::add_player($player);
        foreach ($this->course_array as $course) {
            $course->catch_up($player);
        }
    }
    
    
    public function remove_player($player)
    {
        if (isset($player->course_id)) {
            $course = $this->course_array[$player->course_id];
            $course->clear_slot($player);
        }
        Room::remove_player($player);
    }
}
