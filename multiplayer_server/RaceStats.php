<?php

namespace pr2\multi;

class RaceStats
{

    public $temp_id;
    public $user_id;
    public $name;
    public $group;
    public $mod_power;
    public $rank;
    public $ip;

    public $finish_time;
    public $drawing = true;
    public $still_here = true;
    public $finished_race = false;
    public $quit_race = false;

    public $level_hash = '';
    public $mode = '';
    public $finish_positions = '';
    public $finish_count = '';
    public $cowboy_chance = '';

    public $eggs = 0;
    public $objectives_reached = array();
    public $last_objective_time = 0;

    public function __construct($player)
    {
        $this->temp_id = $player->temp_id;
        $this->user_id = $player->user_id;
        $this->name = $player->name;
        $this->group = $player->group;
        $this->mod_power = mod_power($player);
        $this->rank = $player->active_rank;
        $this->ip = $player->ip;
    }
}
