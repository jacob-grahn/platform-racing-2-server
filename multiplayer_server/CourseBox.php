<?php

class CourseBox {

	public $slot_array = array();
	public $course_id;
	public $room;
	private $force_time;

	public function __construct($course_id, $room){
		$this->course_id = $course_id;
		$this->room = $room;

		$room->course_array[$course_id] = $this;
	}

	public function fill_slot($player, $slot){
		if(!isset($this->slot_array[$slot])){
			if(isset($player->course_box)){
				$player->course_box->clear_slot($player);
			}
			$player->confirmed = false;
			$player->slot = $slot;
			$player->course_box = $this;
			$this->slot_array[$slot] = $player;
			$this->room->send_to_room($this->get_fill_str($player, $slot), $player->user_id);
			$player->write($this->get_fill_str($player, $slot).'`me');

			if(isset($this->force_time)){
				$player->write('forceTime`'.(time()-$this->force_time));
			}
		}
	}

	public function confirm_slot($player){
		if($player->confirmed == false){
			$player->confirmed = true;
			$this->room->send_to_all($this->get_confirm_str($player->slot));
		}

		if(!isset($this->force_time)){
			$this->force_time = time();
			$this->send_to_all('forceTime`0');
		}

		$this->check_confirmed();
	}

	public function clear_slot($player){
		$slot = $player->slot;

		$player->confirmed = false;
		$player->slot = NULL;
		$player->course_box = NULL;

		$this->slot_array[$slot] = NULL;
		unset($this->slot_array[$slot]);
		$this->room->send_to_all($this->get_clear_str($slot));

		if($this->count_confirmed() <= 0){
			$this->force_time = NULL;
			$this->send_to_all('forceTime`-1');
		}

		if(count($this->slot_array) <= 0){
			$this->remove();
		}
		else{
			$this->check_confirmed();
		}
	}

	public function catch_up($to_player){
		foreach($this->slot_array as $player){
			$to_player->write($this->get_fill_str($player, $player->slot));
			if($player->confirmed){
				$to_player->write($this->get_confirm_str($player->slot));
			}
		}
	}

	private function get_fill_str($player, $slot){
		return 'fillSlot'.$this->course_id.'`'.$slot.'`'.$player->name.'`'.$player->active_rank;
	}

	private function get_confirm_str($slot){
		return 'confirmSlot'.$this->course_id.'`'.$slot;
	}

	private function get_clear_str($slot){
		return 'clearSlot'.$this->course_id.'`'.$slot;
	}

	private function check_confirmed(){
		$all_confirmed = true;
		foreach($this->slot_array as $player){
			if(!$player->confirmed){
				$all_confirmed = false;
				break;
			}
		}
		if($all_confirmed){
			$this->start_game();
		}
	}

	private function start_game(){
		$course_id = substr($this->course_id, 0, strpos($this->course_id, '_'));
		$game = new Game($course_id);
		foreach($this->slot_array as $player){
			$player->confirmed = false;
			$game->add_player($player);
			client_set_right_room($player->socket, 'none');
			client_set_chat_room($player->socket, 'none');
		}
		$game->init();
		$this->remove();
	}

	public function force_start(){
		if((time() - $this->force_time) > 15){
			foreach($this->slot_array as $player){
				if(!$player->confirmed){
					$this->clear_slot($player);
					$player->write('closeCourseMenu`');
				}
			}
		}
	}

	private function send_to_all($str){
		foreach($this->slot_array as $player){
			$player->socket->write($str);
		}
	}

	public function send_to_room($str, $from_id){
		foreach($this->slot_array as $player){
			if($player->user_id != $from_id){
				$player->socket->write($str);
			}
		}
	}

	private function count_confirmed(){
		$num = 0;
		foreach($this->slot_array as $player){
			if($player->confirmed){
				$num++;
			}
		}
		return $num;
	}

	public function remove(){
		foreach($this->slot_array as $player){
			$this->clear_slot($player);
		}

		$this->slot_array = NULL;
		unset($this->slot_array);

		$this->room->course_array[$this->course_id] = NULL;
		unset($this->room->course_array[$this->course_id]);
	}
}
