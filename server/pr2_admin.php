<?php



function become_process($socket, $data) {
  global $PROCESS_PASS;
	if($data == $PROCESS_PASS) {
		$socket->process = true;
	}
}


//--- sends a message to a player -----------------------------------------
function send_message_to_player( $socket, $data ) {
	if($socket->process == true) {
		$obj = json_decode( $data );
		$user_id = $obj->user_id;
		$message = $obj->message;

		$player = id_to_player( $user_id, false );
		if( isset($player) ) {
			$player->write( 'message`' . $message );
		}
		$socket->write('{"status":"ok"}');
	}
}



//--- creates a player if the log in was successful -----------------------
function register_login($socket, $data){

	if($socket->process == true){
		global $login_array;
		global $player_array;
		global $guild_id;
		global $guild_owner;

		$login_obj = json_decode( $data );
		$login_id = $login_obj->login->login_id;
		$group = $login_obj->user->power;
		$user_id = $login_obj->user->user_id;

		$socket = @$login_array[$login_id];
		unset($login_array[$login_id]);

		if(isset($socket)){

			if( $guild_id != 0 && $guild_id != $login_obj->user->guild ) {
				$socket->write('message`You are not a member of this guild.');
				$socket->close();
				$socket->on_disconnect();
			}

			else if(isset($player_array[$user_id])){
				$existing_player = $player_array[$user_id];
				$existing_player->write('message`You were disconnected because you logged in somewhere else.');
				$existing_player->remove();

				$socket->write('message`Your account was already running on this server. It has been logged out to save your data. Please log in again.');
				$socket->close();
				$socket->on_disconnect();
			}

			else if( LocalBans::is_banned($login_obj->user->name) ) {
				$socket->write('message`You have been kicked from this server for 30 minutes.');
				$socket->close();
				$socket->on_disconnect();
			}

			else {
				$player = new Player( $socket, $login_obj );
				$socket->player = $player;
				if( $player->user_id == $guild_owner ) {
					$player->become_temp_mod();
				}
				$socket->write('loginSuccessful`'.$group);
				$socket->write('setRank`'.$player->active_rank);
				$socket->write( 'ping`' . time() );
				/*if( $tournament ) {
					$socket->write( "message`Welcome to the tournament sever! Here you can prove your skills in fair matches. \n* The results of matches are posted in the chat. \n* All players have their speed, acceleration, and jumping set to 65. \n* No hats can be worn. \n* No exp or prizes can be won." );
				}*/
			}
		}
	}
}


//--- disconnects if the log in failed -------------------------------------
function fail_login($socket, $data){
	if($socket->process == true){
		global $login_array;

		list($login_id, $message) = explode('`', $data);

		$socket = $login_array[$login_id];
		unset($login_array[$login_id]);

		if(is_object($socket)){
			$socket->write('message`'.$message);
			$socket->on_disconnect();
			$socket->close();
		}
	}
}



//--- outputs a message from a process --------------------------------------
function process_message($socket, $data){
	if($socket->process == true){
		output($data);
	}
}



//--- shutdown ----------------------------------------------------------------
function shut_down($socket, $data){
	if($socket->process == true){
		output('received shut down command...');
		$socket->write('shuting_down');
		shutdown_server();
	}
}



//--- unlock super booster -------------------------------------------------------------
function unlock_super_booster( $socket, $data ) {
	if( $socket->process == true ) {
		$user_id = $data;
		$player = id_to_player( $user_id, false );
		if( isset( $player ) ) {
			$player->super_booster = true;
		}
		$socket->write('ok`');
	}
}




//--- update cycle -----------------------------------------------------------
function update_cycle( $socket, $data ) {
	if( $socket->process == true ) {
		$obj = json_decode( $data );
		place_artifact( $obj->artifact );
		pm_notify( $obj->recent_pms );
		apply_bans( $obj->recent_bans );

		$rep = new stdClass();
		$rep->plays = drain_plays();
		$rep->gp = GuildPoints::drain();
		$rep->population = get_population();
		$rep->status = get_status();
		$rep->happy_hour = pr2_server::$happy_hour;

		$socket->write( json_encode( $rep ) );
	}
}


//--- clear player's daily exp levels -------------------------------------------------
function start_new_day( $socket, $data ) {
	if( $socket->process == true ) {
		global $player_array;
		foreach( $player_array as $player ) {
			$player->start_exp_today = $player->exp_today = 0;
		}
		$socket->write('day-ok');
	}
}


//--- mark an ip as a good human ------------------------------------------------------
function mark_human($socket, $data) {
	if($socket->process == true) {
		$ip = $data;
		global $player_array;
		foreach( $player_array as $player ) {
			if($player->ip == $ip && $player->human == false) {
				$player->human = true;
				$exp_gain = $player->hostage_exp_points + 250;
				$player->inc_exp($exp_gain);
			}
		}
	}
}


//--- mark an ip as a bad robot --------------------------------------------------------
function mark_robot( $socket, $data ) {
	if($socket->process == true) {
		$ip = $data;
		global $player_array;
		Robots::add($ip);
		foreach( $player_array as $player ) {
			if($player->ip == $ip) {
				$player->human = false;
				$player->hostage_exp_points = 0;
			}
		}
	}
}


function check_status($socket, $data){
	$socket->write('ok');
}

?>
