<?php

class Player {

	public $socket;
	public $user_id;
	public $guild_id;

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
		global $port;
		global $server_name;
		global $server_expire_time;
		global $db;

		// find what room the player is in
		if(isset($this->chat_room) && !isset($this->game_room)) {
			$room_type = "c"; // c for chat
			$player_room = $this->chat_room;
		}
		else if(isset($this->game_room) && !isset($this->chat_room)) {
			$room_type = "g"; // g for game
			$player_room = $this->game_room;
		}
		// this should never happen
		else if(isset($this->chat_room) && isset($this->game_room)) {
			$room_type = "b"; // b for both
		}
		// this also should never happen
		else {
			$room_type = "n"; // n for none
		}

		//special text emotes
		$chat_message = str_replace(":shrug:", "¯\_(ツ)_/¯", $chat_message);

		// html killer for systemChat
		$safe_chat_message = htmlspecialchars($chat_message);

		// switch for text effects
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

		// make sure they're in exactly one valid room
		if($room_type != 'n' && $room_type != 'b' && isset($player_room)) {

			// guest check
			if($this->group <= 0) {
				$this->write('systemChat`Sorries, guests can\'t send chat messages.');
			}
			// chat ban check (warnings, auto-warn)
			else if($this->chat_ban > time()) {
				$this->write('systemChat`You have been temporarily banned from the chat. '
					.'The ban will be lifted in '.($this->chat_ban - time()).' seconds.');
			}
			// spam check
			else if($this->get_chat_count() > 6) {
				$this->chat_ban = time() + 60;
				$this->write('systemChat`Slow down a bit, yo.');
			}
			// rank 3 check
			else if($this->active_rank < 3) {
				$this->write('systemChat`Sorries, you must be rank 3 or above to chat.');
			}
			// illegal character check
			else if(strpos($chat_message, '`') !== false) {
				$this->write('message`Error: Illegal character in message.');
			}
			// tournament mode
			else if(strpos($chat_message, '/t ') === 0 || strpos($chat_message, '/tournament ') === 0 || $chat_message == '/t' || $chat_message == '/tournament') {
				// if guild owner, allow them to do guild owner things
				if ($this->user_id == $guild_owner) {
					// help
					if($chat_message == '/t help' || $chat_message == '/t' || $chat_message == '/tournament') {
						$this->write('systemChat`Welcome to tournament mode!<br><br>To enable a tournament, use /t followed by a hat name and stat values for the desired speed, acceleration, and jumping of the tournament.<br><br>Example: /t none 65 65 65<br>Hat: None<br>Speed: 65<br>Accel: 65<br>Jump: 65<br><br>To turn off tournament mode, type /t off. To find out whether tournament mode is on or off, type /t status.');
					}
					// status
					else if($chat_message == '/t status') {
						tournament_status($this);
					}
					// tournament mode
					else {
						try {
							//handle exceptions
							$caught_exception = false;

							// attempt to start a tournament using the specified parameters
							issue_tournament($safe_chat_message);
						}
						catch (Exception $e) {
							$caught_exception = true;
							$err = $e->getMessage();
							$err_supl = " Make sure you typed everything correctly! For help with tournaments, type /t help.";
							$this->write('systemChat`Error: ' . $err . $err_supl);
						}

						// if an error was not encountered, announce the tournament to the chatroom
						if(!$caught_exception) {
							announce_tournament($player_room);
						}
					}
				}
				// if not the guild owner, limit their ability to checking the status of a tournament only
				else {
					// status
					if ($chat_message == '/t status' || $chat_message == '/t' || $chat_message == '/tournament') {
						tournament_status($this);
					}
					// tell them how to get the status
					else {
						$this->write('systemChat`To find out whether tournament mode is on or off, type /t status.');
					}
				}
			}
			// chat effects
			else if(!is_null($chat_effect) && $this->group >= 2) {
				if($room_type == 'c') {
					$player_room->send_chat('systemChat`' . $chat_effect_tag . $this->name . ' has temporarily activated ' . $chat_effect . ' chat!');
				}
				else {
					$this->write('systemChat`This command cannot be used in levels.');
				}
			}
			// chat announcements
			else if(strpos($chat_message, '/a ') === 0 && ($this->group >= 2 || $this->user_id == $guild_owner)) {
				$announcement = trim(substr($chat_message, 3));
				$safe_announcement = htmlspecialchars($announcement); // html killer
				$announce_length = strlen($safe_announcement);

				if($announce_length >= 1) {
					$player_room->send_chat('systemChat`Chatroom Announcement from '.$this->name.': ' . $safe_announcement);
				}
				else {
					$this->write('systemChat`Your announcement must be at least 1 character.');
				}
			}
			// "give" command
			else if(strpos($chat_message, '/give ') === 0 && ($this->group >= 2 || $this->user_id == $guild_owner)) {
				$give_this = trim(substr($chat_message, 6));
				$safe_give_this = htmlspecialchars($give_this); // html killer
				$give_this_length = strlen($safe_give_this);

				if($give_this_length >= 1) {
					$player_room->send_chat('systemChat`'.$this->name.' has given ' . $safe_give_this);
				}
				else {
					$this->write('systemChat`The thing you\'re giving must be at least 1 character.');
				}
			}
			// "promote" command
			else if(strpos($chat_message, '/promote ') === 0 && $this->group >= 3) {
				$promote_this = trim(substr($chat_message, 9));
				$safe_promote_this = htmlspecialchars($promote_this); // html killer
				$promote_this_length = strlen($safe_promote_this);

				if($promote_this_length >= 1) {
					$player_room->send_chat('systemChat`'.$this->name.' has promoted ' . $safe_promote_this);
				}
				else {
					$this->write('systemChat`The thing you\'re promoting must be at least 1 character.');
				}
			}
			// population command
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
			// clear command
			else if (($chat_message == '/clear' || $chat_message == '/cls') && $this->group >= 2) {
				if($player_room == $this->chat_room) {
					$player_room->clear();
				}
				else {
					$this->write('systemChat`This command cannot be used in levels.');
				}
			}
			// restart server command for admins
			else if (($chat_message == '/restart_server' || strpos($chat_message, '/restart_server ') === 0) && $this->group >= 3) {
				$admin_name = $this->name;
				$admin_id = $this->user_id;
				$ip = $this->ip;
				
				if ($room_type == 'c') {
					if ($chat_message == '/restart_server yes, i am sure!') {
						
						try {
							
							// check to see if the server name has been defined...
							if(!isset($server_name) || empty($server_name)) {
								throw new Exception('Unable to retrieve the server name. The port number has been logged instead.');
							}
							
							// log action in action log
							$db->call('admin_action_insert', array($admin_id, "$admin_name restarted $server_name from $ip.", $admin_id, $ip));
							
							// shut it down, yo
							shutdown_server();
							
						}
						catch(Exception $e) {
							$message = $e->getMessage();
							$this->write("message`Error: $message");
							
							// log it with the port instead of the server name
							$db->call('admin_action_insert', array($admin_id, "$admin_name restarted the server running on port $port from $ip.", $admin_id, $ip));
							
							// shut it down, yo
							shutdown_server();
						}
					}
					else {
						$this->write('systemChat`WARNING: You just typed the server restart command. If you choose to proceed, this action will disconnect EVERY player on this server. Are you sure you want to disconnect ALL players and restart the server? If so, type: /restart_server yes, i am sure!');
					}
				}
				else {
					$this->write('systemChat`This command cannot be used in levels.');
				}
			}
			// time left in a private server command
			else if ($chat_message == '/timeleft' && $this->user_id == $guild_owner) {
				if ($server_id > 10) {
					$this->write("systemChat`Your server will expire on $server_expire_time. To extend your time, buy either Private Server 1 or Private Server 30 from the Vault of Magics.");
				}
				else {
					$this->write("systemChat`This is not a private server.");
				}
			}
			// help command
			else if ($chat_message == '/help' || $chat_message == '/commands' || $chat_message == '/?' || $chat_message == '/') {
				$server_owner_supplement = '';
				$staff_supplement = '';
				$admin_supplement = '';

				if ($room_type == 'g') {
					$this->write('systemChat`To get a list of commands that can be used in the chatroom, go to the chat tab in the lobby and type /help.');
				}
				else {
					if ($this->group >= 2) {
						$staff_supplement = '<br>Moderator:<br>- /a (Announcement)<br>- /give *text*<br>- /clear<br>Chat Effects:<br>- /b (Bold)<br>- /i (Italics)<br>- /u (Underlined)<br>- /li (Bulleted)';
	
						if ($this->group >= 3) {
							$admin_supplement = '<br>Admin:<br>- /promote *message*<br>- /restart_server';
						}
						if ($this->user_id == $guild_owner) {
							$server_owner_supplement = '<br>Server Owner:<br>- /timeleft<br>- /t (Tournament)<br>For more information on tournaments, use /t help.';
						}
					}
					$this->write('systemChat`PR2 Chat Commands:<br>- /view *player*<br>- /guild *guild name*<br>- /hint (Artifact)<br>- /t status<br>- /population'.$staff_supplement.$admin_supplement.$server_owner_supplement);
				}
			}
			// send chat message
			else {
				if (strpos($this->name, '`') !== false) {
					$this->write('message`Error: Illegal character in username.');
				}
				else {
					$message = 'chat`'.$this->name.'`'.$this->group.'`'.$chat_message;
					$this->chat_count++;
					$this->chat_time = time();
					$player_room->send_chat($message, $this->user_id);
				}
			}
		}
		// this should never happen
		else if ($room_type == 'b') {
			$this->write('message`Error: You can\'t be in two places at once!');
		}
		// this should also never happen
		else if ($room_type == 'n') {
			$this->write('message`Error: You don\'t seem to be in a valid chatroom.');
		}
		// aaaaand this most certainly will never happen
		else {
			$this->write('message`Error: The server encountered an error when trying to determine what chatroom you\'re in. Try rejoining the chatroom and sending your message again.');
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
		if(HappyHour::isActive()) {
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



	public function save_info () {
		global $port;
		global $server_id;
		global $db;

		// auto removing some hat?
		$index = array_search(27, $this->hat_array);
		if($index !== false) {
			array_splice($this->hat_array, $index, 1);
		}

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

		if( $status == 'offline' ) {
			$e_server_id = 0;
		}

		if($this->group == 0) {
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
	}



	public function remove() {
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
	}
}


?>
