<?php

namespace pr2\multi;

class Room
{

    public $player_array = array();
    protected $room_name = '';

    public function addPlayer($player)
    {
        if (isset($player->{$this->room_name})) {
            $player->{$this->room_name}->removePlayer($player);
        }
        $player->{$this->room_name} = $this;
        $this->player_array[$player->user_id] = $player;
    }

    public function removePlayer($player)
    {
        $this->player_array[$player->user_id] = null;
        unset($this->player_array[$player->user_id]);

        $player->{$this->room_name} = null;
        unset($player->{$this->room_name});
    }

    public function sendToRoom($str, $from_id)
    {
        foreach ($this->player_array as $player) {
            if ($player->user_id != $from_id) {
                $player->write($str);
            }
        }
    }

    public function sendToAll($str)
    {
        foreach ($this->player_array as $player) {
            $player->write($str);
        }
    }

    public function remove()
    {
        $this->player_array = null;
        $this->room_name = null;
    }
}
