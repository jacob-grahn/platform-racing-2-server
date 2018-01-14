<?php

class Player {

	public $socket;
	public $user_id;
	public $guild_id;
	public $human;
<<<<<<< HEAD
	
=======

>>>>>>> shell-fix-prodemote
	public $name;
	public $rank;
	public $active_rank;
	public $exp_points;
	public $start_exp_today;
	public $exp_today;
	public $group;
<<<<<<< HEAD
	
=======

>>>>>>> shell-fix-prodemote
	public $hat_color;
	public $head_color;
	public $body_color;
	public $feet_color;
<<<<<<< HEAD
	
=======

>>>>>>> shell-fix-prodemote
	public $hat_color_2;
	public $head_color_2;
	public $body_color_2;
	public $feet_color_2;
<<<<<<< HEAD
	
=======

>>>>>>> shell-fix-prodemote
	public $hat;
	public $head;
	public $body;
	public $feet;
<<<<<<< HEAD
	
=======

>>>>>>> shell-fix-prodemote
	public $hat_array = array();
	public $head_array = array();
	public $body_array = array();
	public $feet_array = array();
<<<<<<< HEAD
	
=======

>>>>>>> shell-fix-prodemote
	public $epic_hat_array = array();
	public $epic_head_array = array();
	public $epic_body_array = array();
	public $epic_feet_array = array();
<<<<<<< HEAD
	
	public $speed;
	public $acceleration;
	public $jumping;
	
	public $friends;
	public $ignored;
	
	public $rt_used;
	public $rt_available;
	
	public $url = '';
	public $version = '0.0';
	
	public $last_action = 0;
	public $chat_count = 0;
	public $chat_time = 0;
	
	public $right_room;
	public $chat_room;
	public $game_room;
	
	public $course_box;
	public $confirmed = false;
	public $slot;
	
	public $temp_id;
	public $pos_x = 0;
	public $pos_y = 0;
	
	public $worn_hat_array = array();
	public $finished_race = false;
	public $quit_race = false;
	
	public $chat_ban = 0;
	
	public $domain;
	public $ip;
	
	public $temp_mod = false;
	
	public $status = '';
	
=======

	public $speed;
	public $acceleration;
	public $jumping;

	public $friends;
	public $ignored;

	public $rt_used;
	public $rt_available;

	public $url = '';
	public $version = '0.0';

	public $last_action = 0;
	public $chat_count = 0;
	public $chat_time = 0;

	public $right_room;
	public $chat_room;
	public $game_room;

	public $course_box;
	public $confirmed = false;
	public $slot;

	public $temp_id;
	public $pos_x = 0;
	public $pos_y = 0;

	public $worn_hat_array = array();
	public $finished_race = false;
	public $quit_race = false;

	public $chat_ban = 0;

	public $domain;
	public $ip;

	public $temp_mod = false;

	public $status = '';

>>>>>>> shell-fix-prodemote
	public $lux = 0;
	public $lives = 3;
	public $items_used = 0;
	public $artifact = 0;
	public $super_booster = false;
	public $last_save_time = 0;

<<<<<<< HEAD
	
	public function __construct($socket, $login) {									
		$this->socket = $socket;
		$this->ip = $socket->ip;	
		
		$this->user_id = $login->user->user_id;	
		$this->name = $login->user->name;
		$this->group = $login->user->power;
		$this->guild_id = $login->user->guild;
		
		$this->rank = $login->stats->rank;
		$this->exp_points = $login->stats->exp_points;
		
=======

	public function __construct($socket, $login) {
		$this->socket = $socket;
		$this->ip = $socket->ip;

		$this->user_id = $login->user->user_id;
		$this->name = $login->user->name;
		$this->group = $login->user->power;
		$this->guild_id = $login->user->guild;

		$this->rank = $login->stats->rank;
		$this->exp_points = $login->stats->exp_points;

>>>>>>> shell-fix-prodemote
		$this->hat_color = $login->stats->hat_color;
		$this->head_color = $login->stats->head_color;
		$this->body_color = $login->stats->body_color;
		$this->feet_color = $login->stats->feet_color;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$this->hat_color_2 = $login->stats->hat_color_2;
		$this->head_color_2 = $login->stats->head_color_2;
		$this->body_color_2 = $login->stats->body_color_2;
		$this->feet_color_2 = $login->stats->feet_color_2;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$this->hat = $login->stats->hat;
		$this->head = $login->stats->head;
		$this->body = $login->stats->body;
		$this->feet = $login->stats->feet;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$this->hat_array = explode(",", $login->stats->hat_array);
		$this->head_array = explode(",", $login->stats->head_array);
		$this->body_array = explode(",", $login->stats->body_array);
		$this->feet_array = explode(",", $login->stats->feet_array);
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if( isset($login->epic_upgrades->epic_hats) ) {
			$this->epic_hat_array = $this->safe_explode( $login->epic_upgrades->epic_hats );
			$this->epic_head_array = $this->safe_explode( $login->epic_upgrades->epic_heads );
			$this->epic_body_array = $this->safe_explode( $login->epic_upgrades->epic_bodies );
			$this->epic_feet_array = $this->safe_explode( $login->epic_upgrades->epic_feet );
		}
<<<<<<< HEAD
		
		$this->speed = $login->stats->speed;
		$this->acceleration = $login->stats->acceleration;
		$this->jumping = $login->stats->jumping;
		
		$this->friends_array = $login->friends;
		$this->ignored_array = $login->ignored;
		
		$this->domain = $login->login->domain;
		$this->version = $login->login->version;
		
=======

		$this->speed = $login->stats->speed;
		$this->acceleration = $login->stats->acceleration;
		$this->jumping = $login->stats->jumping;

		$this->friends_array = $login->friends;
		$this->ignored_array = $login->ignored;

		$this->domain = $login->login->domain;
		$this->version = $login->login->version;

>>>>>>> shell-fix-prodemote
		$this->rt_used = $login->rt_used;
		$this->rt_available = $login->rt_available;
		$this->exp_today = $this->start_exp_today = $login->exp_today;
		$this->artifact = $login->artifact;
		$this->status = $login->status;
<<<<<<< HEAD
	
=======

>>>>>>> shell-fix-prodemote
		$socket->player = $this;
		$this->active_rank = $this->rank + $this->rt_used;
		$this->last_save_time = time();
		$this->human = !Robots::is_robot($this->ip);
		$this->hostage_exp_points = 0;
<<<<<<< HEAD
		
		global $player_array;
		global $max_players;
		
=======

		global $player_array;
		global $max_players;

>>>>>>> shell-fix-prodemote
		if((count($player_array) > $max_players && $this->group < 2) || (count($player_array) > ($max_players-10) && $this->group == 0)){
			$this->write('loginFailure`');
			$this->write('message`Sorry, this server is full. Try back later.');
			$this->remove();
		}
		else{
			$player_array[$this->user_id] = $this;
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$this->award_kong_hat();
		$this->apply_temp_items();
		$this->verify_stats();
		$this->verify_parts();
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	private function safe_explode( $str_arr ) {
		if( isset($str_arr) && strlen($str_arr) > 0 ) {
			$arr = explode( ',', $str_arr );
		}
		else {
			$arr = array();
		}
		return $arr;
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	private function apply_temp_items() {
		$temp_items = TemporaryItems::get_items( $this->user_id, $this->guild_id );
		foreach( $temp_items as $item ) {
			$this->gain_part( 'e'.ucfirst($item->type), $item->part_id );
			$this->set_part( $item->type, $item->part_id, true );
		}
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function inc_exp($exp) {
		if(Robots::is_robot($this->ip)) {
			$exp = 0;
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$max_rank = RankupCalculator::get_exp_required($this->active_rank+1);
		$this->write('setExpGain`'.$this->exp_points.'`'.($this->exp_points+$exp).'`'.$max_rank);
		$this->exp_points += $exp;
		$this->exp_today += $exp;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		//rank up
		if($this->exp_points >= $max_rank){
			$this->rank++;
			$this->active_rank++;
			$this->exp_points = ($this->exp_points - $max_rank);
			$this->write('setRank`'.$this->active_rank);
		}
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function inc_rank($inc){
		$this->rank += $inc;
		if($this->rank < 0){
			$this->rank = 0;
		}
		$this->active_rank = $this->rank + $this->rt_used;
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function maybe_save() {
		$time = time();
		if( $time - $this->last_save_time > 60*15 ) {
			$this->last_save_time = $time;
			$this->save_info();
		}
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function use_rank_token() {
		if($this->rt_used < $this->rt_available) {
			$this->rt_used++;
		}
		$this->active_rank = $this->rank + $this->rt_used;
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function unuse_rank_token() {
		if($this->rt_used > 0) {
			$this->rt_used--;
		}
		$this->active_rank = $this->rank + $this->rt_used;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if( $this->speed + $this->acceleration + $this->jumping > 150 + $this->active_rank ) {
			$this->speed--;
		}
		$this->verify_stats();
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function send_chat($chat_message) {
		if($this->group <= 0) {
			$this->write('systemChat`Sorries, guests can\'t send chat messages.');
		}
		else if($this->chat_ban > time()) {
<<<<<<< HEAD
			$this->write('systemChat`You have been temporarily banned from the chat. '
=======
			$this->write('systemChat`You have been temporarially banned from the chat. '
>>>>>>> shell-fix-prodemote
				.'The ban will be lifted in '.($this->chat_ban - time()).' seconds.');
		}
		else if($this->get_chat_count() > 6) {
			$this->chat_ban = time() + 60;
			$this->write('systemChat`Slow down a bit, yo.');
		}
		else if($this->active_rank < 3) {
			$this->write('systemChat`Sorries, you must be rank 3 or above to chat.');
		}
<<<<<<< HEAD
		else if( strpos($chat_message, '/tournament ') === 0 || strpos($chat_message, '/t ') === 0 || $chat_message == '/t' ) {
=======
		else if( strpos($chat_message, '/tournament') === 0 || strpos($chat_message, '/t') === 0 ) {
>>>>>>> shell-fix-prodemote
			global $guild_owner;
			if( $this->user_id == $guild_owner ) {
				issue_tournament( $chat_message );
				if( isset($this->chat_room) ) {
					announce_tournament( $this->chat_room );
				}
			}
			else {
				$this->write('systemChat`Such powers are reserved for owners of private servers.');
			}
		}
		else {
			$message = 'chat`'.$this->name.'`'.$this->group.'`'.$chat_message;
			$this->chat_count++;
			$this->chat_time = time();
			if(isset($this->chat_room)) {
				$this->chat_room->send_chat($message, $this->user_id);
			}
			else if(isset($this->game_room)) {
				$this->game_room->send_chat($message, $this->user_id);
			}
		}
	}
<<<<<<< HEAD
	
	
	
=======



>>>>>>> shell-fix-prodemote
	private function get_chat_count(){
		$seconds = time() - $this->chat_time;
		$this->chat_count -= $seconds / 2;
		if($this->chat_count < 0){
			$this->chat_count = 0;
		}
		return $this->chat_count;
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function is_ignored_id($id){
		if(array_search($id, $this->ignored_array) === false){
			return false;
		}
		else{
			return true;
		}
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function send_customize_info() {
		$this->socket->write('setCustomizeInfo'
						.'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
						.'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
						.'`'.join(',', $this->get_full_parts( 'hat' ))
						.'`'.join(',', $this->get_full_parts( 'head' ))
						.'`'.join(',', $this->get_full_parts( 'body' ))
						.'`'.join(',', $this->get_full_parts( 'feet' ))
						.'`'.$this->get_real_stat_str()
						.'`'.$this->active_rank
						.'`'.$this->rt_used.'`'.$this->rt_available
						.'`'.$this->hat_color_2.'`'.$this->head_color_2.'`'.$this->body_color_2.'`'.$this->feet_color_2
						.'`'.join(',', $this->get_full_parts( 'eHat' ))
						.'`'.join(',', $this->get_full_parts( 'eHead' ))
						.'`'.join(',', $this->get_full_parts( 'eBody' ))
						.'`'.join(',', $this->get_full_parts( 'eFeet' ))
		);
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function get_remote_info() {
		return 'createRemoteCharacter'
				.'`'.$this->temp_id.'`'.$this->name
				.'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
				.'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
				.'`'.$this->get_second_color('hat', $this->hat).'`'.$this->get_second_color('head', $this->head).'`'.$this->get_second_color('body', $this->body).'`'.$this->get_second_color('feet', $this->feet);
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function get_local_info() {
		return 'createLocalCharacter'
				.'`'.$this->temp_id
				.'`'.$this->get_stat_str()
				.'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
				.'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
				.'`'.$this->get_second_color('hat', $this->hat).'`'.$this->get_second_color('head', $this->head).'`'.$this->get_second_color('body', $this->body).'`'.$this->get_second_color('feet', $this->feet);
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function get_second_color( $type, $id ) {
		if( $type === 'hat' ) {
			$color = $this->hat_color_2;
			$epic_arr = $this->epic_hat_array;
		}
		else if( $type === 'head' ) {
			$color = $this->head_color_2;
			$epic_arr = $this->epic_head_array;
		}
		else if( $type === 'body' ) {
			$color = $this->body_color_2;
			$epic_arr = $this->epic_body_array;
		}
		else if( $type === 'feet' ) {
			$color = $this->feet_color_2;
			$epic_arr = $this->epic_feet_array;
		}
<<<<<<< HEAD
		
		if( array_search($id, $epic_arr) === false && array_search('*', $epic_arr) === false ) {
			$color = -1;
		}
		
		return( $color );
	}		
	
	
=======

		if( array_search($id, $epic_arr) === false && array_search('*', $epic_arr) === false ) {
			$color = -1;
		}

		return( $color );
	}


>>>>>>> shell-fix-prodemote
	public function award_kong_hat() {
		if( strpos($this->domain, 'kongregate.com') !== false ) {
			$added = $this->gain_part( 'hat', 3, true );
			if( $added ) {
				$this->hat_color = 10027008;
			}
		}
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	public function award_kong_outfit() {
		$this->gain_part( 'head', 20, true );
		$this->gain_part( 'body', 17, true );
		$this->gain_part( 'feet', 16, true );
	}
<<<<<<< HEAD
	
	
	
=======



>>>>>>> shell-fix-prodemote
	public function gain_part($type, $id, $autoset=false) {
		if( $type === 'hat' ) {
			$arr = &$this->hat_array;
		}
		else if( $type === 'head' ) {
			$arr = &$this->head_array;
		}
		else if( $type === 'body' ) {
			$arr = &$this->body_array;
		}
		else if( $type === 'feet' ) {
			$arr = &$this->feet_array;
		}
		else if( $type === 'eHat' ) {
			$arr = &$this->epic_hat_array;
		}
		else if( $type === 'eHead' ) {
			$arr = &$this->epic_head_array;
		}
		else if( $type === 'eBody' ) {
			$arr = &$this->epic_body_array;
		}
		else if( $type === 'eFeet' ) {
			$arr = &$this->epic_feet_array;
		}
		else {
			throw new Exception( 'Player::gain_part - unknown part type' );
		}
<<<<<<< HEAD
			
=======

>>>>>>> shell-fix-prodemote
		if( isset($arr) && array_search($id, $arr) === false ) {
			array_push( $arr, $id );
			if( $autoset ) {
				$this->set_part( $type, $id );
			}
			return true;
		}
		else {
			return false;
		}
	}
<<<<<<< HEAD
	
	
	
=======



>>>>>>> shell-fix-prodemote
	public function set_part( $type, $id ) {
		if( strpos($type, 'e') === 0 ) {
			$type = substr( $type, 1 );
			$type = strtolower( $type );
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if($type == 'hat') {
			$this->hat = $id;
		}
		else if( $type == 'head' ) {
			$this->head = $id;
		}
		else if( $type == 'body' ) {
			$this->body = $id;
		}
		else if( $type == 'feet' ) {
			$this->feet = $id;
		}
	}
<<<<<<< HEAD
	
	
	
=======



>>>>>>> shell-fix-prodemote
	private function get_stat_str() {
		if(pr2_server::$happy_hour) {
			$speed = 100;
			$accel = 100;
			$jump = 100;
		}
		else if( pr2_server::$tournament ) {
			$speed = pr2_server::$tournament_speed;
			$accel = pr2_server::$tournament_acceleration;
			$jump = pr2_server::$tournament_jumping;
		}
		else {
			$speed = $this->speed;
			$accel = $this->acceleration;
			$jump = $this->jumping;
		}
		if( $this->super_booster ) {
			$speed += 10;
			$accel += 10;
			$jump += 10;
		}
		$str = "$speed`$accel`$jump";
		return $str;
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	private function get_real_stat_str() {
		$speed = $this->speed;
		$accel = $this->acceleration;
		$jump = $this->jumping;
		$str = "$speed`$accel`$jump";
		return $str;
	}
<<<<<<< HEAD
	
	
	public function set_customize_info($data){
		list($hat_color, $head_color, $body_color, $feet_color, 
				$hat_color_2, $head_color_2, $body_color_2, $feet_color_2,
				$hat, $head, $body, $feet, 
				$speed, $acceleration, $jumping) = explode('`', $data);
				
=======


	public function set_customize_info($data){
		list($hat_color, $head_color, $body_color, $feet_color,
				$hat_color_2, $head_color_2, $body_color_2, $feet_color_2,
				$hat, $head, $body, $feet,
				$speed, $acceleration, $jumping) = explode('`', $data);

>>>>>>> shell-fix-prodemote
		$this->hat_color = $hat_color;
		$this->head_color = $head_color;
		$this->body_color = $body_color;
		$this->feet_color = $feet_color;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if( $hat_color_2 != -1 )
			$this->hat_color_2 = $hat_color_2;
		if( $head_color_2 != -1 )
			$this->head_color_2 = $head_color_2;
		if( $body_color_2 != -1 )
			$this->body_color_2 = $body_color_2;
		if( $feet_color_2 != -1 )
			$this->feet_color_2 = $feet_color_2;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$this->hat = $hat;
		$this->head = $head;
		$this->body = $body;
		$this->feet = $feet;
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if( $speed + $acceleration + $jumping <= 150 + $this->active_rank ) {
			$this->speed = $speed;
			$this->acceleration = $acceleration;
			$this->jumping = $jumping;
		}
<<<<<<< HEAD
		
		$this->verify_parts();
		$this->verify_stats();
		$this->save_info();
	}
	
	
	
=======

		$this->verify_parts();
		$this->verify_stats();
	}



>>>>>>> shell-fix-prodemote
	private function verify_stats() {
		if( $this->speed < 0 ) {
			$this->speed = 0;
		}
		if( $this->acceleration < 0 ) {
			$this->acceleration = 0;
		}
		if( $this->jumping < 0 ) {
			$this->jumping = 0;
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if( $this->speed > 100 ) {
			$this->speed = 100;
		}
		if( $this->acceleration > 100 ) {
			$this->acceleration = 100;
		}
		if( $this->jumping > 100 ) {
			$this->jumping = 100;
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if( $this->speed + $this->acceleration + $this->jumping > 150 + $this->active_rank ) {
			$this->speed = 50;
			$this->acceleration = 50;
			$this->jumping = 50;
		}
	}
<<<<<<< HEAD
	
	
	
=======



>>>>>>> shell-fix-prodemote
	private function verify_parts( $strict=false ) {
		$this->verify_part($strict, 'hat');
		$this->verify_part($strict, 'head');
		$this->verify_part($strict, 'body');
		$this->verify_part($strict, 'feet');
	}
<<<<<<< HEAD
	
	
	
	private function verify_part($strict, $type) {		
		$eType = 'e'.ucfirst($type);
		$part = $this->{$type};
		
=======



	private function verify_part($strict, $type) {
		$eType = 'e'.ucfirst($type);
		$part = $this->{$type};

>>>>>>> shell-fix-prodemote
		if( $strict ) {
			$parts_available = $this->get_owned_parts( $type );
			$epic_parts_available = $this->get_owned_parts( $eType );
		}
		else {
			$parts_available = $this->get_full_parts( $type );
			$epic_parts_available = $this->get_full_parts( $eType );
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		if( array_search($part, $parts_available) === false ) {
			$part = $parts_available[0];
			$this->{$type} = $part;
		}
		/*if( array_search($part, $epic_parts_available) === false ) {
			$this->{$type.'_color_2'} = -1;
		}*/
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	private function get_owned_parts( $type ) {
		if(substr($type, 0, 1) == 'e') {
			$arr = $this->{'epic_'.strtolower(substr($type,1)).'_array'};
		}
		else {
			$arr = $this->{$type.'_array'};
		}
		return $arr;
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	private function get_rented_parts( $type ) {
		$arr = TemporaryItems::get_parts( $type, $this->user_id, $this->guild_id );
		return $arr;
	}
<<<<<<< HEAD
	
	
=======


>>>>>>> shell-fix-prodemote
	private function get_full_parts( $type ) {
		$perm = $this->get_owned_parts( $type );
		$temp = $this->get_rented_parts( $type );
		$full = array_merge( $perm, $temp );
		return $full;
<<<<<<< HEAD
	}	
	
	
=======
	}


>>>>>>> shell-fix-prodemote
	public function write($str){
		if(isset($this->socket)){
			$this->socket->write($str);
		}
	}
<<<<<<< HEAD
	
	
	
=======



>>>>>>> shell-fix-prodemote
	public function wearing_hat($hat_num) {
		$wearing = false;
		foreach($this->worn_hat_array as $hat){
			if($hat->num === $hat_num) {
				$wearing = true;
			}
		}
		return $wearing;
	}
<<<<<<< HEAD
	
	
	
=======



>>>>>>> shell-fix-prodemote
	public function become_temp_mod() {
		$this->group = 2;
		$this->temp_mod = true;
		$this->write('becomeTempMod`');
	}
<<<<<<< HEAD
	
	
	
	public function save_info(){
		global $port;
		global $import_path;
		global $server_id;
		
=======



	public function save_info(){
		global $port;
		global $server_id;

>>>>>>> shell-fix-prodemote
		$index = array_search(27, $this->hat_array);
		if($index !== false) {
			array_splice($this->hat_array, $index, 1);
		}
<<<<<<< HEAD
		
		$user_id = escapeshellarg($this->user_id);
		
=======

		$user_id = escapeshellarg($this->user_id);

>>>>>>> shell-fix-prodemote
		$name = escapeshellarg($this->name);
		$rank = escapeshellarg($this->rank);
		$exp_points = escapeshellarg($this->exp_points);
		$group = escapeshellarg($this->group);
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$hat_color = escapeshellarg($this->hat_color);
		$head_color = escapeshellarg($this->head_color);
		$body_color = escapeshellarg($this->body_color);
		$feet_color = escapeshellarg($this->feet_color);
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$hat_color_2 = escapeshellarg($this->hat_color_2);
		$head_color_2 = escapeshellarg($this->head_color_2);
		$body_color_2 = escapeshellarg($this->body_color_2);
		$feet_color_2 = escapeshellarg($this->feet_color_2);
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$hat = escapeshellarg($this->hat);
		$head = escapeshellarg($this->head);
		$body = escapeshellarg($this->body);
		$feet = escapeshellarg($this->feet);
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$hat_array = escapeshellarg(join(',', $this->hat_array));
		$head_array = escapeshellarg(join(',', $this->head_array));
		$body_array = escapeshellarg(join(',', $this->body_array));
		$feet_array = escapeshellarg(join(',', $this->feet_array));
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		$epic_hat_array = escapeshellarg(join(',', $this->epic_hat_array));
		$epic_head_array = escapeshellarg(join(',', $this->epic_head_array));
		$epic_body_array = escapeshellarg(join(',', $this->epic_body_array));
		$epic_feet_array = escapeshellarg(join(',', $this->epic_feet_array));
<<<<<<< HEAD
		
		$speed = escapeshellarg($this->speed);
		$acceleration = escapeshellarg($this->acceleration);
		$jumping = escapeshellarg($this->jumping);
		
		$status = escapeshellarg( $this->status );
		$e_server_id = escapeshellarg( $server_id );
		
		$lux = escapeshellarg($this->lux);
		$this->lux = 0;
		
		$rt_used = escapeshellarg($this->rt_used);
		$ip = escapeshellarg($this->ip);
		$tot_exp_gained = escapeshellarg($this->exp_today - $this->start_exp_today);
		
=======

		$speed = escapeshellarg($this->speed);
		$acceleration = escapeshellarg($this->acceleration);
		$jumping = escapeshellarg($this->jumping);

		$status = escapeshellarg( $this->status );
		$e_server_id = escapeshellarg( $server_id );

		$lux = escapeshellarg($this->lux);
		$this->lux = 0;

		$rt_used = escapeshellarg($this->rt_used);
		$ip = escapeshellarg($this->ip);
		$tot_exp_gained = escapeshellarg($this->exp_today - $this->start_exp_today);

>>>>>>> shell-fix-prodemote
		if($this->group == 0){
			$rank = 0;
			$exp_points = 0;
			$hat_array = '1';
			$head_array = '1,2,3,4,5,6,7,8,9';
			$body_array = '1,2,3,4,5,6,7,8,9';
			$feet_array = '1,2,3,4,5,6,7,8,9';
			$epic_hat_array = '';
			$epic_head_array = '';
			$epic_body_array = '';
			$epic_feet_array = '';
			$hat = 1;
			$head = 1;
			$body = 1;
			$feet = 1;
			$rt_used = 0;
			$speed = 50;
			$acceleration = 50;
			$jumping = 50;
		}
<<<<<<< HEAD
		
		$update_str = 'nohup php '.$import_path.'/commands/update_pr2_user.php '.$port.' '.$user_id
=======

		$update_str = 'nohup php '.__DIR__.'/commands/update_pr2_user.php '.$port.' '.$user_id
>>>>>>> shell-fix-prodemote
		.' '.$name.' '.$rank.' '.$exp_points.' '.$group
		.' '.$hat_color.' '.$head_color.' '.$body_color.' '.$feet_color
		.' '.$hat.' '.$head.' '.$body.' '.$feet
		.' '.$hat_array.' '.$head_array.' '.$body_array.' '.$feet_array
		.' '.$speed.' '.$acceleration.' '.$jumping
		.' '.$status
		.' '.$lux
		.' '.$rt_used
		.' '.$ip
		.' '.$tot_exp_gained
		.' '.$e_server_id
		.' '.$hat_color_2.' '.$head_color_2.' '.$body_color_2.' '.$feet_color_2
		.' '.$epic_hat_array.' '.$epic_head_array.' '.$epic_body_array.' '.$epic_feet_array
		.' > /dev/null &';
<<<<<<< HEAD
		
		exec($update_str);
	}
	
	
	
	public function remove(){
		global $player_array;
		
		unset($player_array[$this->user_id]);
		
=======

		exec($update_str);
	}



	public function remove(){
		global $player_array;

		unset($player_array[$this->user_id]);

>>>>>>> shell-fix-prodemote
		//make sure the socket is nice and dead
		if(is_object($this->socket)){
			unset($this->socket->player);
			if($this->socket->disconnected === false){
				$this->socket->close();
				$this->socket->on_disconnect();
			}
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		//get out of whatever you're in
		if(isset($this->right_room)){
			$this->right_room->remove_player($this);
		}
		if(isset($this->chat_room)){
			$this->chat_room->remove_player($this);
		}
		if(isset($this->game_room)){
			$this->game_room->remove_player($this);
		}
		if(isset($this->course_box)){
			$this->course_box->clear_slot($this);
		}
<<<<<<< HEAD
		
=======

>>>>>>> shell-fix-prodemote
		//save info
		$this->status = "offline";
		$this->verify_stats();
		$this->verify_parts( true );
		$this->save_info();
<<<<<<< HEAD
		
		//delete
		//echo "removing player: ".$this->name."\n";
		
=======

		//delete
		//echo "removing player: ".$this->name."\n";

>>>>>>> shell-fix-prodemote
		$this->socket = NULL;
		$this->user_id = NULL;
		$this->name = NULL;
		$this->rank = NULL;
		$this->active_rank = NULL;
		$this->exp_points = NULL;
		$this->group = NULL;
		$this->hat_color = NULL;
		$this->head_color = NULL;
		$this->body_color = NULL;
		$this->feet_color = NULL;
		$this->hat = NULL;
		$this->head = NULL;
		$this->body = NULL;
		$this->feet = NULL;
		$this->hat_array = NULL;
		$this->head_array = NULL;
		$this->body_array = NULL;
		$this->feet_array = NULL;
		$this->epic_hat_array = NULL;
		$this->epic_head_array = NULL;
		$this->epic_body_array = NULL;
		$this->epic_feet_array = NULL;
		$this->speed = NULL;
		$this->acceleration = NULL;
		$this->jumping = NULL;
		$this->friends = NULL;
		$this->ignored = NULL;
		$this->url = NULL;
		$this->version = NULL;
		$this->last_action = NULL;
		$this->chat_count = NULL;
		$this->chat_time = NULL;
		$this->right_room = NULL;
		$this->chat_room = NULL;
		$this->game_room = NULL;
		$this->course_box = NULL;
		$this->confirmed = NULL;
		$this->slot = NULL;
		$this->temp_id = NULL;
		$this->pos_x = NULL;
		$this->pos_y = NULL;
		$this->worn_hat_array = NULL;
		$this->finished_race = NULL;
		$this->quit_race = NULL;
		$this->chat_ban = NULL;
		$this->domain = NULL;
		$this->temp_mod = NULL;
		$this->status = NULL;

		unset($this);
	}
}


?>
