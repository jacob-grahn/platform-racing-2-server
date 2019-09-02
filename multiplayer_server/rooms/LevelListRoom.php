<?php

namespace pr2\multi;

class LevelListRoom extends Room
{

    protected $room_name = 'right_room';
    public $course_array = array();
    private $type = 'none';


    public function __construct($type)
    {
        $this->type = $type;
        LoiterDetector::addLevelList($this);
    }


    public function addPlayer($player)
    {
        Room::addPlayer($player);
        foreach ($this->course_array as $course) {
            $course->catchUp($player);
        }
        $this->refreshHighlights($player);
    }


    public function removePlayer($player)
    {
        if (isset($player->course_id)) {
            $course = $this->course_array[$player->course_id];
            $course->clearSlot($player);
        }
        Room::removePlayer($player);
    }


    // adds a page highlight for a new coursebox if one isn't already active for this page
    public function maybeHighlight($box, $mode, int $page)
    {
        $box->room = $this;

        $max = $this->type === 'campaign' ? 6 : 9;
        if ($page < 1 || $page > $max || ($mode !== 'add' && $mode !== 'remove') || $this->type === 'search') {
            return; // don't continue when out of bounds of nav, invalid mode)
        }

        foreach ($this->course_array as $course) {
            if ($course->page_number === $page) {
                return; // don't continue if a coursebox is already/still active on the page
            }
        }

        $this->sendToAll("{$mode}PageHighlight`$page");
    }


    public function refreshHighlights($player)
    {
        if ($this->type === 'search') {
            return; // don't continue if on search
        }

        foreach ($this->course_array as $course) {
            $page = $course->page_number;
            if (is_numeric($page) && $page > 0 && $page < 10 && !isset(${'pg' . $page})) {
                ${'pg' . $page} = true;
                $player->socket->write("addPageHighlight`$page");
            }
        }
    }


    public function fillSlot($player, $course_id, $slot, $page_num)
    {
        if (!is_numeric($slot) || $slot < 0 || $slot > 3) {
            $slot = 0;
        }
        if (isset($this->course_array[$course_id])) {
            $this->course_array[$course_id]->fillSlot($player, $slot);
        } else {
            $course = new CourseBox($this, $course_id, $page_num);
            $course->fillSlot($player, $slot);
        }
    }
}
