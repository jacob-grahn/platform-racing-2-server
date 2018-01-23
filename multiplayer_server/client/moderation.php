<?php

// call pro/demotion functions
require_once(__DIR__ . '/../fns/promote_to_moderator.php');
require_once(__DIR__ . '/../fns/demod.php');


//--- kick a player -------------------------------------------------------------
function client_kick ($socket, $data) {
	global $guild_owner;
	$name = $data;
	$safe_name = htmlspecialchars($name); // convert name to htmlspecialchars for html exploit patch
	$kicked_player = name_to_player($name);

	$player = $socket->get_player();

	// if the player actually has the power to do what they're trying to do, then do it
	if(($player->group >= 2) && (($kicked_player->group < 2) || ($player->user_id == $guild_owner))) {

		LocalBans::add($name);

		if( isset($kicked_player) ) {
			$kicked_player->remove();
			$player->write('message`'.$safe_name.' has been kicked from this server for 30 minutes.');
		}

		// let people know that the player kicked someone
		if(isset($player->chat_room)){
			$player->chat_room->send_chat('systemChat`'.$player->name
			.' has kicked '.$safe_name.' from this server for 30 minutes.', $player->user_id);
		}
	}
	// if they don't have the power to do that, tell them
	else {
		$player->write('message`Error: You lack the power to kick '.$safe_name.'.');
	}
}


//--- warn a player -------------------------------------------------------------
function client_warn ($socket, $data) {
	list($name, $num) = explode("`", $data);
	$safe_name = htmlspecialchars($name); // convert name to htmlspecialchars for html exploit patch

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
			.' has given '.$safe_name.' '.$num.' '.$w_str.'. '
			.'They have been banned from the chat for '.$time.' seconds.', $player->user_id);
		}
	}
	// if they aren't a mod, tell them
	else {
		$player->write('message`Error: You lack the power to warn '.$safe_name.'.');
	}
}




//--- ban a player -------------------------------------------------------
function client_ban ($socket, $data) {
	list($banned_name, $seconds, $reason) = explode("`", $data);

	$player = $socket->get_player();
	$ban_player = name_to_player($banned_name);
	$safe_name = htmlspecialchars($banned_name); // convert name to htmlspecialchars for html exploit patch
	$safe_reason = htmlspecialchars($reason);
	$mod_id = $player->user_id;

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
		$disp_reason = 'Reason: '.$safe_reason;
	}

	if($player->group >= 2){
		if(isset($player->chat_room)) {
			$player->chat_room->send_chat('systemChat`'.$player->name
			.' has banned '.$safe_name.' for '.$disp_time.'. '.$disp_reason.'. This ban has been recorded at https://pr2hub.com/bans.', $player->user_id);
		}
		if(isset($ban_player) && $ban_player->group < 2) {
			$ban_player->remove();
		}
	}
}



//--- promote a player to a moderator -------------------------------------
function client_promote_to_moderator ($socket, $data) {
	list($name, $type) = explode("`", $data);
	$from_player = $socket->get_player();
	$to_player = name_to_player($name); // define before if
	$safe_name = htmlspecialchars($name); // convert name to htmlspecialchars for html exploit patch

	// if they're an admin, continue with the promotion (1st line of defense)
	if($from_player->group > 2){

		global $port;
		promote_mod($port, $name, $type, $from_player, $to_player);

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
			.' has promoted '.$safe_name
			.' to a '.$type.' moderator! May they reign in '.$reign_time.' of peace and prosperity! Make sure you read the moderator guidelines at jiggmin2.com/forums/showthread.php?tid=12', $from_player->user_id);
		}
	}
	// if they're not an admin, tell them
	else {
		$from_player->write('message`Error: You lack the power to promote '.$safe_name.' to a '.$type.' moderator.');
	}
}


//-- demote a moderator ------------------------------------------------------------------
function client_demote_moderator ($socket, $name) {
	$from_player = $socket->get_player();

	if($from_player->group == 3){
		global $port;
		$to_player = name_to_player($name);

		demote_mod($port, $name, $from_player, $to_player);
	}
}


?>
