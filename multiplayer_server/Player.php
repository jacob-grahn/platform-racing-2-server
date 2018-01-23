<?php

<<<<<<< HEAD:server/Player.php
require_once(__DIR__ . '/../commands/update_pr2_user.php');

=======
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
class Player {

	public $socket;
	public $user_id;
	public $guild_id;
	public $human;

	public $name;
	public $rank;
	public $active_rank;
	public $exp_points;
	public $start_exp_today;
	public $exp_today;
	public $group;

	public $hat_color;
	public $head_color;
	public $body_color;
	public $feet_color;

	public $hat_color_2;
	public $head_color_2;
	public $body_color_2;
	public $feet_color_2;

	public $hat;
	public $head;
	public $body;
	public $feet;

	public $hat_array = array();
	public $head_array = array();
	public $body_array = array();
	public $feet_array = array();

	public $epic_hat_array = array();
	public $epic_head_array = array();
	public $epic_body_array = array();
	public $epic_feet_array = array();

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

	public $lux = 0;
	public $lives = 3;
	public $items_used = 0;
	public $artifact = 0;
	public $super_booster = false;
	public $last_save_time = 0;


	public function __construct($socket, $login) {
		$this->socket = $socket;
		$this->ip = $socket->ip;

		$this->user_id = $login->user->user_id;
		$this->name = $login->user->name;
		$this->group = $login->user->power;
		$this->guild_id = $login->user->guild;

		$this->rank = $login->stats->rank;
		$this->exp_points = $login->stats->exp_points;

		$this->hat_color = $login->stats->hat_color;
		$this->head_color = $login->stats->head_color;
		$this->body_color = $login->stats->body_color;
		$this->feet_color = $login->stats->feet_color;

		$this->hat_color_2 = $login->stats->hat_color_2;
		$this->head_color_2 = $login->stats->head_color_2;
		$this->body_color_2 = $login->stats->body_color_2;
		$this->feet_color_2 = $login->stats->feet_color_2;

		$this->hat = $login->stats->hat;
		$this->head = $login->stats->head;
		$this->body = $login->stats->body;
		$this->feet = $login->stats->feet;

		$this->hat_array = explode(",", $login->stats->hat_array);
		$this->head_array = explode(",", $login->stats->head_array);
		$this->body_array = explode(",", $login->stats->body_array);
		$this->feet_array = explode(",", $login->stats->feet_array);

		if( isset($login->epic_upgrades->epic_hats) ) {
			$this->epic_hat_array = $this->safe_explode( $login->epic_upgrades->epic_hats );
			$this->epic_head_array = $this->safe_explode( $login->epic_upgrades->epic_heads );
			$this->epic_body_array = $this->safe_explode( $login->epic_upgrades->epic_bodies );
			$this->epic_feet_array = $this->safe_explode( $login->epic_upgrades->epic_feet );
		}

		$this->speed = $login->stats->speed;
		$this->acceleration = $login->stats->acceleration;
		$this->jumping = $login->stats->jumping;

		$this->friends_array = $login->friends;
		$this->ignored_array = $login->ignored;

		$this->domain = $login->login->domain;
		$this->version = $login->login->version;

		$this->rt_used = $login->rt_used;
		$this->rt_available = $login->rt_available;
		$this->exp_today = $this->start_exp_today = $login->exp_today;
		$this->artifact = $login->artifact;
		$this->status = $login->status;

		$socket->player = $this;
		$this->active_rank = $this->rank + $this->rt_used;
		$this->last_save_time = time();
		$this->human = !Robots::is_robot($this->ip);
		$this->hostage_exp_points = 0;

		global $player_array;
		global $max_players;

		if((count($player_array) > $max_players && $this->group < 2) || (count($player_array) > ($max_players-10) && $this->group == 0)){
			$this->write('loginFailure`');
			$this->write('message`Sorry, this server is full. Try back later.');
			$this->remove();
		}
		else{
			$player_array[$this->user_id] = $this;
		}

		$this->award_kong_hat();
		$this->apply_temp_items();
		$this->verify_stats();
		$this->verify_parts();
	}


	private function safe_explode( $str_arr ) {
		if( isset($str_arr) && strlen($str_arr) > 0 ) {
			$arr = explode( ',', $str_arr );
		}
		else {
			$arr = array();
		}
		return $arr;
	}


	private function apply_temp_items() {
		$temp_items = TemporaryItems::get_items( $this->user_id, $this->guild_id );
		foreach( $temp_items as $item ) {
			$this->gain_part( 'e'.ucfirst($item->type), $item->part_id );
			$this->set_part( $item->type, $item->part_id, true );
		}
	}


	public function inc_exp($exp) {
		if(Robots::is_robot($this->ip)) {
			$exp = 0;
		}

		$max_rank = RankupCalculator::get_exp_required($this->active_rank+1);
		$this->write('setExpGain`'.$this->exp_points.'`'.($this->exp_points+$exp).'`'.$max_rank);
		$this->exp_points += $exp;
		$this->exp_today += $exp;

		//rank up
		if($this->exp_points >= $max_rank){
			$this->rank++;
			$this->active_rank++;
			$this->exp_points = ($this->exp_points - $max_rank);
			$this->write('setRank`'.$this->active_rank);
		}
	}


	public function inc_rank($inc){
		$this->rank += $inc;
		if($this->rank < 0){
			$this->rank = 0;
		}
		$this->active_rank = $this->rank + $this->rt_used;
	}


	public function maybe_save() {
		$time = time();
		if( $time - $this->last_save_time > 60*15 ) {
			$this->last_save_time = $time;
			$this->save_info();
		}
	}


	public function use_rank_token() {
		if($this->rt_used < $this->rt_available) {
			$this->rt_used++;
		}
		$this->active_rank = $this->rank + $this->rt_used;
	}


	public function unuse_rank_token() {
		if($this->rt_used > 0) {
			$this->rt_used--;
		}
		$this->active_rank = $this->rank + $this->rt_used;

		if( $this->speed + $this->acceleration + $this->jumping > 150 + $this->active_rank ) {
			$this->speed--;
		}
		$this->verify_stats();
	}


	public function send_chat($chat_message) {
		global $guild_owner;
		global $player_array;
<<<<<<< HEAD:server/Player.php
		$chat_message = htmlspecialchars($chat_message); // html killer
		
=======
		$safe_chat_message = htmlspecialchars($chat_message); // html killer for systemChat

>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
		switch($chat_message) {
			case '/b':
				$chat_effect = 'bold';
				$chat_effect_tag = '<b>';
				break;
			case '/i':
				$chat_effect = 'italicized';
				$chat_effect_tag = '<i>';
				break;
			case '/u':
				$chat_effect = 'underlined';
				$chat_effect_tag = '<u>';
				break;
			case '/li':
				$chat_effect = 'bulleted';
				$chat_effect_tag = '<li>';
				break;
			default:
				$chat_effect = NULL;
				$chat_effect_tag = NULL;
				break;
		}
<<<<<<< HEAD:server/Player.php
		
=======

>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
		if($this->group <= 0) {
			$this->write('systemChat`Sorries, guests can\'t send chat messages.');
		}
		else if($this->chat_ban > time()) {
			$this->write('systemChat`You have been temporarily banned from the chat. '
				.'The ban will be lifted in '.($this->chat_ban - time()).' seconds.');
		}
		else if($this->get_chat_count() > 6) {
			$this->chat_ban = time() + 60;
			$this->write('systemChat`Slow down a bit, yo.');
		}
		else if($this->active_rank < 3) {
			$this->write('systemChat`Sorries, you must be rank 3 or above to chat.');
		}
<<<<<<< HEAD:server/Player.php
		else if( strpos($chat_message, '/tournament ') === 0 || strpos($chat_message, '/t ') === 0 || $chat_message == '/t' ) {
			global $guild_owner;
			if( $this->user_id == $guild_owner ) {
				issue_tournament( $chat_message );
				if( isset($this->chat_room) ) {
					announce_tournament( $this->chat_room );
=======
		else if(strpos($chat_message, '`') !== false) {
			$this->write('message`Error: Illegal character in message.');
		}
		else if(strpos($chat_message, '/tournament ') === 0 || strpos($chat_message, '/t ') === 0 || $chat_message == '/t') {
			if($this->user_id == $guild_owner) {
				if ($chat_message == '/t help') {
					$this->write('systemChat`Welcome to tournament mode! To enable a tournament, use /t followed by a hat name and stat values for the desired speed, acceleration, and jumping of the tournament.<br><br>Example: /t exp 65 65 65<br>Hat: EXP<br>Speed: 65<br>Accel: 65<br>Jump: 65<br><br>To turn off tournament mode, type /t off.');
				}
				else {
					issue_tournament($safe_chat_message);
					if(isset($this->chat_room)) {
						announce_tournament($this->chat_room);
					}
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
				}
			}
			else {
				$this->write('systemChat`Such powers are reserved for owners of private servers.');
			}
		}
		else if(!is_null($chat_effect)) {
			if ( $this->group >= 2 ) {
				$this->chat_room->send_chat('systemChat`' . $chat_effect_tag . $this->name .
							    ' has temporarily activated ' . $chat_effect . ' chat!', $this->user_id);
			}
			else {
				$this->write('systemChat`Such powers are reserved for owners of private servers and the PR2 staff team.');
			}
		}
<<<<<<< HEAD:server/Player.php
		else if(strpos($chat_message, '/a ')) {
			$announcement = trim(substr($chat_message, 3));
			$announce_length = strlen($announcement);
			
			if ($this->group >= 2 || $this->user_id == $guild_owner) {
				if($announce_length >= 1) {
					$this->chat_room->send_chat('systemChat`' . $announcement, $this->user_id);
=======
		else if(strpos($chat_message, '/a ') === 0) {
			$announcement = trim(substr($chat_message, 3));
			$safe_announcement = htmlspecialchars($announcement); // html killer
			$announce_length = strlen($safe_announcement);

			if ($this->group >= 2 || $this->user_id == $guild_owner) {
				if($announce_length >= 1) {
					$this->chat_room->send_chat('systemChat`Chatroom Announcement from '.$this->name.': ' . $safe_announcement, $this->user_id);
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
				}
				else {
					$this->write('systemChat`Your announcement must be at least 1 character.');
				}
			}
			else {
<<<<<<< HEAD:server/Player.php
				$this->write('systemChat`Only owners of private servers and the PR2 staff team may make chatroom announcements.');
			}
		}
		else if($chat_message == '/pop' || $chat_message == '/population') {
			$this->write('systemChat`There are currently '.count($player_array).' people on this server.');
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
=======
				$this->write('systemChat`Only owners of private servers and members of the PR2 staff team may make chatroom announcements.');
			}
		}
		else if(strpos($chat_message, '/give ') === 0) {
			$givingthis = trim(substr($chat_message, 6));
			$safe_givingthis = htmlspecialchars($givingthis); // html killer
			$givingthis_length = strlen($safe_givingthis);

			if ($this->group >= 2 || $this->user_id == $guild_owner) {
				if($givingthis_length >= 1) {
					$this->chat_room->send_chat('systemChat`'.$this->name.' has given ' . $safe_givingthis, $this->user_id);
				}
				else {
					$this->write('systemChat`The thing you\'re giving must be at least 1 character.');
				}
			}
			else {
				$this->write('systemChat`Only owners of private servers and members of the PR2 staff team may use this command.');
			}
		}
		else if($chat_message == '/pop' || $chat_message == '/population') {
			$pop_counted = count($player_array); // count players
			$pop_singular = array("is", "user"); // language for 1 player
			$pop_plural = array("are", "users"); // language for multiple players

			if ($pop_counted === 1) {
				$pop_lang = $pop_singular; // if there is only one player, associate the singular language with the called variable
			}
			else {
				$pop_lang = $pop_plural; // if there is more than one player, associate the plural language with the called variable
			}

			$this->write('systemChat`There '.$pop_lang[0].' currently '.$pop_counted.' '.$pop_lang[1].' playing on this server.');
		}
		else if (($chat_message == '/clear' || $chat_message == '/cls') && $this->group >= 2) {
			$this->chat_room->clear();
		}
		else if ($chat_message == '/help' || $chat_message == '/?' || $chat_message == '/') {
			$server_owner_supplement = '';
			$staff_supplement = '';

			if ($this->group >= 2) {
				$staff_supplement = '<br>- /a (Announcement)<br>- /give *text*<br>Chat Effects:<br>- /b (Bold)<br>- /i (Italics)<br>- /u (Underlined)<br>- /li (Bulleted)';

				if ($this->user_id == $guild_owner) {
					$server_owner_supplement = '<br>Server Owner:<br>- /t (Tournament)<br>For more information on tournaments, use /t help.';
				}
			}
			$this->write('systemChat`PR2 Chat Commands:<br>- /view *player*<br>- /guild *guild name*<br>- /population'.$staff_supplement.$server_owner_supplement);
		}
		else {
			if (strpos($this->name, '`') !== false) {
				$this->write('message`Error: Illegal character in username.');
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
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
			}
		}
	}



	private function get_chat_count(){
		$seconds = time() - $this->chat_time;
		$this->chat_count -= $seconds / 2;
		if($this->chat_count < 0){
			$this->chat_count = 0;
		}
		return $this->chat_count;
	}


	public function is_ignored_id($id){
		if(array_search($id, $this->ignored_array) === false){
			return false;
		}
		else{
			return true;
		}
	}


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


	public function get_remote_info() {
		return 'createRemoteCharacter'
				.'`'.$this->temp_id.'`'.$this->name
				.'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
				.'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
				.'`'.$this->get_second_color('hat', $this->hat).'`'.$this->get_second_color('head', $this->head).'`'.$this->get_second_color('body', $this->body).'`'.$this->get_second_color('feet', $this->feet);
	}


	public function get_local_info() {
		return 'createLocalCharacter'
				.'`'.$this->temp_id
				.'`'.$this->get_stat_str()
				.'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
				.'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
				.'`'.$this->get_second_color('hat', $this->hat).'`'.$this->get_second_color('head', $this->head).'`'.$this->get_second_color('body', $this->body).'`'.$this->get_second_color('feet', $this->feet);
	}


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

		if( array_search($id, $epic_arr) === false && array_search('*', $epic_arr) === false ) {
			$color = -1;
		}

		return( $color );
	}


	public function award_kong_hat() {
		if( strpos($this->domain, 'kongregate.com') !== false ) {
			$added = $this->gain_part( 'hat', 3, true );
			if( $added ) {
				$this->hat_color = 10027008;
			}
		}
	}


	public function award_kong_outfit() {
		$this->gain_part( 'head', 20, true );
		$this->gain_part( 'body', 17, true );
		$this->gain_part( 'feet', 16, true );
	}



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



	public function set_part( $type, $id ) {
		if( strpos($type, 'e') === 0 ) {
			$type = substr( $type, 1 );
			$type = strtolower( $type );
		}

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


	private function get_real_stat_str() {
		$speed = $this->speed;
		$accel = $this->acceleration;
		$jump = $this->jumping;
		$str = "$speed`$accel`$jump";
		return $str;
	}


	public function set_customize_info($data){
		list($hat_color, $head_color, $body_color, $feet_color,
				$hat_color_2, $head_color_2, $body_color_2, $feet_color_2,
				$hat, $head, $body, $feet,
				$speed, $acceleration, $jumping) = explode('`', $data);

		$this->hat_color = $hat_color;
		$this->head_color = $head_color;
		$this->body_color = $body_color;
		$this->feet_color = $feet_color;

		if( $hat_color_2 != -1 )
			$this->hat_color_2 = $hat_color_2;
		if( $head_color_2 != -1 )
			$this->head_color_2 = $head_color_2;
		if( $body_color_2 != -1 )
			$this->body_color_2 = $body_color_2;
		if( $feet_color_2 != -1 )
			$this->feet_color_2 = $feet_color_2;

		$this->hat = $hat;
		$this->head = $head;
		$this->body = $body;
		$this->feet = $feet;

		if( $speed + $acceleration + $jumping <= 150 + $this->active_rank ) {
			$this->speed = $speed;
			$this->acceleration = $acceleration;
			$this->jumping = $jumping;
		}

		$this->verify_parts();
		$this->verify_stats();
		$this->save_info();
	}



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

		if( $this->speed > 100 ) {
			$this->speed = 100;
		}
		if( $this->acceleration > 100 ) {
			$this->acceleration = 100;
		}
		if( $this->jumping > 100 ) {
			$this->jumping = 100;
		}

		if( $this->speed + $this->acceleration + $this->jumping > 150 + $this->active_rank ) {
			$this->speed = 50;
			$this->acceleration = 50;
			$this->jumping = 50;
		}
	}



	private function verify_parts( $strict=false ) {
		$this->verify_part($strict, 'hat');
		$this->verify_part($strict, 'head');
		$this->verify_part($strict, 'body');
		$this->verify_part($strict, 'feet');
	}



	private function verify_part($strict, $type) {
		$eType = 'e'.ucfirst($type);
		$part = $this->{$type};

		if( $strict ) {
			$parts_available = $this->get_owned_parts( $type );
			$epic_parts_available = $this->get_owned_parts( $eType );
		}
		else {
			$parts_available = $this->get_full_parts( $type );
			$epic_parts_available = $this->get_full_parts( $eType );
		}

		if( array_search($part, $parts_available) === false ) {
			$part = $parts_available[0];
			$this->{$type} = $part;
		}
		/*if( array_search($part, $epic_parts_available) === false ) {
			$this->{$type.'_color_2'} = -1;
		}*/
	}


	private function get_owned_parts( $type ) {
		if(substr($type, 0, 1) == 'e') {
			$arr = $this->{'epic_'.strtolower(substr($type,1)).'_array'};
		}
		else {
			$arr = $this->{$type.'_array'};
		}
		return $arr;
	}


	private function get_rented_parts( $type ) {
		$arr = TemporaryItems::get_parts( $type, $this->user_id, $this->guild_id );
		return $arr;
	}


	private function get_full_parts( $type ) {
		$perm = $this->get_owned_parts( $type );
		$temp = $this->get_rented_parts( $type );
		$full = array_merge( $perm, $temp );
		return $full;
	}


	public function write($str){
		if(isset($this->socket)){
			$this->socket->write($str);
		}
	}



	public function wearing_hat($hat_num) {
		$wearing = false;
		foreach($this->worn_hat_array as $hat){
			if($hat->num === $hat_num) {
				$wearing = true;
			}
		}
		return $wearing;
	}



	public function become_temp_mod() {
		$this->group = 2;
		$this->temp_mod = true;
		$this->write('becomeTempMod`');
	}



<<<<<<< HEAD:server/Player.php
	public function save_info(){
		global $port;
		global $server_id;

=======
	public function save_info () {
		global $port;
		global $server_id;
		global $db;

		// auto removing some hat?
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
		$index = array_search(27, $this->hat_array);
		if($index !== false) {
			array_splice($this->hat_array, $index, 1);
		}

<<<<<<< HEAD:server/Player.php
		$user_id = $this->user_id;

		$name = $this->name;
=======
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
		$rank = $this->rank;
		$exp_points = $this->exp_points;
		$group = $this->group;

		$hat_color = $this->hat_color;
		$head_color = $this->head_color;
		$body_color = $this->body_color;
		$feet_color = $this->feet_color;

		$hat_color_2 = $this->hat_color_2;
		$head_color_2 = $this->head_color_2;
		$body_color_2 = $this->body_color_2;
		$feet_color_2 = $this->feet_color_2;

		$hat = $this->hat;
		$head = $this->head;
		$body = $this->body;
		$feet = $this->feet;

		$hat_array = join(',', $this->hat_array);
		$head_array = join(',', $this->head_array);
		$body_array = join(',', $this->body_array);
		$feet_array = join(',', $this->feet_array);

		$epic_hat_array = join(',', $this->epic_hat_array);
		$epic_head_array = join(',', $this->epic_head_array);
		$epic_body_array = join(',', $this->epic_body_array);
		$epic_feet_array = join(',', $this->epic_feet_array);

		$speed = $this->speed;
		$acceleration = $this->acceleration;
		$jumping = $this->jumping;

		$status = $this->status;
		$e_server_id = $server_id;

		$lux = $this->lux;
		$this->lux = 0;

		$rt_used = $this->rt_used;
		$ip = $this->ip;
		$tot_exp_gained = $this->exp_today - $this->start_exp_today;

<<<<<<< HEAD:server/Player.php
		if($this->group == 0){
=======
		if( $status == 'offline' ) {
			$e_server_id = 0;
		}

		if($this->group == 0) {
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
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

<<<<<<< HEAD:server/Player.php
		actually_save_info($port, $name, $rank, $exp_points, $group, $hat_color, $head_color, $body_color, $feet_color, $hat, $head, $body, $feet, $hat_array, $head_array, $body_array, $feet_array, $speed, $acceleration, $jumping, $status, $lux, $rt_used, $ip, $tot_exp_gained, $e_server_id, $hat_color_2, $head_color_2, $body_color_2, $feet_color_2, $epic_hat_array, $epic_head_array, $epic_body_array, $epic_feet_array);
=======
		$db->call( 'pr2_update', array( $this->user_id, $rank, $exp_points,
			$hat_color, $head_color, $body_color, $feet_color,
			$hat_color_2, $head_color_2, $body_color_2, $feet_color_2,
			$hat, $head, $body,
			$feet, $hat_array, $head_array, $body_array, $feet_array,
			$speed, $acceleration, $jumping ),
			MYSQLI_ASYNC
		);

		$db->call( 'epic_upgrades_upsert', array( $this->user_id, $epic_hat_array, $epic_head_array, $epic_body_array, $epic_feet_array ), MYSQLI_ASYNC );
		$db->call( 'user_update_status', array( $this->user_id, $status, $e_server_id ), MYSQLI_ASYNC );
		$db->call( 'rank_token_update', array( $this->user_id, $rt_used ), MYSQLI_ASYNC );
		$db->call( 'exp_today_add', array( 'id-' . $this->user_id, $tot_exp_gained ), MYSQLI_ASYNC ); // todo $exp_today
		$db->call( 'exp_today_add', array( 'ip-' . $ip, $tot_exp_gained ), MYSQLI_ASYNC );
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
	}



<<<<<<< HEAD:server/Player.php
	public function remove(){
=======
	public function remove() {
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
		global $player_array;

		unset($player_array[$this->user_id]);

		//make sure the socket is nice and dead
		if(is_object($this->socket)){
			unset($this->socket->player);
			if($this->socket->disconnected === false){
				$this->socket->close();
				$this->socket->on_disconnect();
			}
		}

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

		//save info
		$this->status = "offline";
		$this->verify_stats();
		$this->verify_parts( true );
		$this->save_info();

		//delete
<<<<<<< HEAD:server/Player.php
		//echo "removing player: ".$this->name."\n";

=======
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
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
<<<<<<< HEAD:server/Player.php

		unset($this);
=======
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/Player.php
	}
}


?>
