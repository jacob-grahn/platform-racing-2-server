<?php


//--- kick a player -------------------------------------------------------------
function kick($socket, $data){
	global $guild_owner;
	$name = $data;
	$kicked_player = name_to_player($name); // define var before line 12 instead of after line 14 for group check

	$player = $socket->get_player();

	// if the player actually has the power to do what they're trying to do, then do it
	if(($player->group >= 2) && (($kicked_player->group < 2) || ($player->user_id == $guild_owner))) {

		LocalBans::add($name);

		if( isset($kicked_player) ) {
			$kicked_player->remove();
			$player->write('message`'.$name.' has been kicked from this server for 30 minutes.');
		}

		// let people know that the player kicked someone
		if(isset($player->chat_room)){
			$player->chat_room->send_chat('systemChat`'.$player->name
			.' has kicked '.$name.' from this server for 30 minutes.', $player->user_id);
		}
	}
	// if they don't have the power to do that, tell them
	else {
		$player->write('message`Error: You lack the power to kick '.$name.'.');
	}
}



//--- warn a player -------------------------------------------------------------
function warn($socket, $data){
	list($name, $num) = explode("`", $data);

	$player = $socket->get_player();

	// if they're a mod, warn the user
	if($player->group >= 2){

		$warned_player = name_to_player($name);

		$w_str = '';
		$time = 0;

		switch($num) {
			case 1:
				$w_str = 'warning';
				$time = 15;
				break;
			case 2:
				$w_str = 'warnings';
				$time = 30;
				break;
			case 3:
				$w_str = 'warnings';
				$time = 60;
				break;
			default:
				$player->write('message`Error: Invalid warning number.');
				break;
		}

		if(isset($warned_player) && $warned_player->group < 2){
			$warned_player->chat_ban = time() + $time;
		}

		if(isset($player->chat_room)){
			$player->chat_room->send_chat('systemChat`'.$player->name
			.' has given '.$name.' '.$num.' '.$w_str.'. '
			.'They have been banned from the chat for '.$time.' seconds.', $player->user_id);
		}
	}
	// if they aren't a mod, tell them
	else {
		$player->write('message`Error: You lack the power to warn '.$name.'.');
	}
}




//--- ban a player -------------------------------------------------------
function ban($socket, $data){
	list($banned_name, $seconds, $reason) = explode("`", $data);

	$player = $socket->get_player();
	$ban_player = name_to_player($banned_name);
	$mod_id = $player->user_id;

	// this is not needed anymore, as temp mods don't have the ban buttons
	/*if($seconds > 60 && $player->temp_mod){
		$seconds = 60;
	}*/

	// set a variable that uses seconds to make friendly times
	switch ($seconds) {
		case 60:
			$disp_time = '1 minute';
			break;
		case 3600:
			$disp_time = '1 hour';
			break;
		case 86400:
			$disp_time = '1 day';
			break;
		case 604800:
			$disp_time = '1 week';
			break;
		case 2419200:
			$disp_time = '1 month';
			break;
		case 29030400:
			$disp_time = '1 year';
			break;
		// if all else fails, echo the seconds
		default:
			$disp_time = $seconds.' seconds';
			break;
	}

	// instead of overwriting the $reason variable, set a new one
	if($reason == ''){
		$disp_reason = 'There was no reason was given';
	}
	if($reason != ''){
		$disp_reason = 'Reason: '.$reason;
	}

	if(isset($ban_player) && $player->group > $ban_player->group){
		if(isset($player->chat_room)) {
			$player->chat_room->send_chat('systemChat`'.$player->name
			.' has banned '.$banned_name.' for '.$disp_time.'. '.$disp_reason.'. This ban has been recorded at http://pr2hub.com/bans.', $player->user_id);
		}
		$ban_player->remove();
	}
}



//--- promote a player to a moderator -------------------------------------
function promote_to_moderator($socket, $data){
	list($name, $type) = explode("`", $data);
	$from_player = $socket->get_player();
	$to_player = name_to_player($name); // define before if

	// if they're an admin and not trying to promote a guest, continue with the promotion
	if(($from_player->group > 2) && ($to_player->group != 0)){
					// promoting guests breaks their accounts
		if(isset($to_player)){
			if($type == 'temporary') {
				$to_player->become_temp_mod();
			}
			else {
				$to_player->group = 2;
				$to_player->write('setGroup`2');
			}
		}

		// give a confirmation message
		$from_player->write('message`'.$name.' has been promoted to a '.$type.' moderator!');

		if($type == 'permanent' || $type == 'trial') {
			global $port;
			$safe_name = escapeshellarg($name);
			exec("nohup php ".__DIR__."/promote_to_moderator.php $port $safe_name $type > /dev/null &");
		}

		switch($type) {
			case 'temporary':
				$reign_time = 'hours';
				break;
			case 'trial':
				$reign_time = 'days';
				break;
			case 'permanent':
				$reign_time = '1000 years';
				break;
		}

		if(isset($from_player->chat_room)){
			$from_player->chat_room->send_chat('systemChat`'.$from_player->name
			.' has promoted '.$name
			.' to a '.$type.' moderator! May they reign in '.$reign_time.' of peace and prosperity! Make sure you read the moderator guidelines at jiggmin2.com/forums/showthread.php?tid=12', $from_player->user_id);
		}
	}
	// if the $to_player isn't online, tell them
	elseif(($from_player->group > 2) && is_null($to_player)) {
		$from_player->write('message`Error: '.$name.' is currently offline or doesn\'t exist.');
	}
	// if they're an admin but trying to promote a guest, tell them
	elseif(($from_player->group > 2) && ($to_player->group == 0)){
		$from_player->write('message`Error: You can\'t promote guests, silly!');
	}
	// if they're not an admin, tell them
	else {
		$from_player->write('message`Error: You lack the power to promote '.$name.' to a '.$type.' moderator.');
	}
}


//-- demote a moderator ------------------------------------------------------------------
function demote_moderator($socket, $name) {
	$from_player = $socket->get_player();

	if($from_player->group == 3){
		$to_player = name_to_player($name);
		if(isset($to_player) && $to_player->group < 2) {
			$from_player->write('message`Error: '.$name.' is not a moderator.');
		}
		if(isset($to_player) && $to_player->group == 2) {
			$to_player->group = 1;
			$to_player->write('setGroup`1');
			$from_player->write('message`'.$name.' has been demoted.');
		}
		global $port;
		$safe_name = escapeshellarg($name);
		exec("nohup php ".__DIR__."/demod.php $port $safe_name > /dev/null &");
	}
}


//--- ban yourself ---
function ban_socket($socket) {
	$player = $socket->get_player();
	exec('nohup php '.__DIR__.'/ban.php '.escapeshellarg($player->user_id).' '.escapeshellarg($socket->remote_address).' '.escapeshellarg($player->name).' > /dev/null &');
	$socket->close();
	$socket->on_disconnect();
}


?>
