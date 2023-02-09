<?php

namespace pr2\multi;

class RaceStats
{

    public $temp_id;
    public $user_id;
    public $name;
    public $rank;
    public $ip;

    public $group;
    public $ca = false;
    public $temp_mod = false;
    public $trial_mod = false;

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
    public $bad_hats = '';

    public $eggs = 0;
    public $objectives_reached = array();
    public $last_objective_time = 0;

    public function __construct($player)
    {
        $this->temp_id = $player->temp_id;
        $this->user_id = $player->user_id;
        $this->name = $player->name;
        $this->rank = $player->active_rank;
        $this->ip = $player->ip;

        $this->group = $player->group;
        $this->ca = $player->ca;
        $this->temp_mod = $player->temp_mod;
        $this->trial_mod = $player->trial_mod;
    }

    public function userify()
    {
        global $player_array;
        $player = @$player_array[$this->user_id];
        return userify(!empty($player) ? $player : $this, $this->name);
    }
}
