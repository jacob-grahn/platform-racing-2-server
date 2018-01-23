<?php

class Room {

	public $player_array = array();
	protected $room_name = '';

	public function add_player($player){
		if(isset($player->{$this->room_name})){
			$player->{$this->room_name}->remove_player($player);
		}
		$player->{$this->room_name} = $this;
		$this->player_array[$player->user_id] = $player;
	}
	
	public function remove_player($player){
		$this->player_array[$player->user_id] = NULL;
		unset($this->player_array[$player->user_id]);
		
		$player->{$this->room_name} = NULL;
		unset($player->{$this->room_name});
	}
	
	public function send_to_room($str, $from_id){
		foreach($this->player_array as $player){
			if($player->user_id != $from_id){
				$player->write($str);
			}
		}
	}
	
	public function send_to_all($str){
		foreach($this->player_array as $player){
			$player->write($str);
		}
	}
	
	public function remove(){
		$this->player_array = NULL;
		$this->room_name = NULL;
	}
}