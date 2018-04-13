<?php

namespace pr2\multi;

class LevelListRoom extends Room
{

    public $course_array = array();
    protected $room_name = 'right_room';


    public function __construct()
    {
        LoiterDetector::addLevelList($this);
    }


    public function addPlayer($player)
    {
        Room::addPlayer($player);
        foreach ($this->course_array as $course) {
            $course->catch_up($player);
        }
    }


    public function removePlayer($player)
    {
        if (isset($player->course_id)) {
            $course = $this->course_array[$player->course_id];
            $course->clear_slot($player);
        }
        Room::removePlayer($player);
    }


    public function fillSlot($player, $course_id, $slot)
    {
        if (!is_numeric($slot) || $slot < 0 || $slot > 3) {
            $slot = 0;
        }
        if (isset($this->course_array[$course_id])) {
            $this->course_array[$course_id]->fillSlot($player, $slot);
        } else {
            $course = new CourseBox($course_id, $this);
            $course->fillSlot($player, $slot);
        }
    }
}
