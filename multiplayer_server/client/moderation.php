<?php

// call pro/demotion functions
require_once(__DIR__ . '/../fns/promote_to_moderator.php');
require_once(__DIR__ . '/../fns/demod.php');


//--- kick a player -------------------------------------------------------------
function client_kick ($socket, $data) {
	global $db, $guild_owner;
	$name = $data;
	$safe_name = htmlspecialchars($name); // convert name to htmlspecialchars for html exploit patch
	$kicked_id = name_to_id($db, $name);
	$kicked_player = id_to_player($kicked_id);

	$player = $socket->get_player();

	// if the player actually has the power to do what they're trying to do, then do it
	if($player->group >= 2 && ($kicked_player->group < 2 || ($player->server_owner == true && $kicked_player != $player))) {

		LocalBans::add($name);

		if(isset($kicked_player)) {
			$kicked_player->remove();
			$player->write("message`$safe_name has been kicked from this server for 30 minutes.");
			
			// let people know that the player kicked someone
			if(isset($player->chat_room)){
				$safe_pname = htmlspecialchars($player->name);
				$player->chat_room->send_chat("systemChat`$safe_pname has kicked $safe_name from this server for 30 minutes.", $player->user_id);
			}
		}
		else {
			$player->write("message`Error: Could not find a user with the name \"$safe_name\" on this server.");
		}
	}
	// if they don't have the power to do that, tell them
	else {
		$player->write("message`Error: You lack the power to kick $safe_name.");
	}
}


//--- warn a player -------------------------------------------------------------
function client_warn ($socket, $data) {
	global $db, $guild_owner;
	list($name, $num) = explode("`", $data);
	$safe_name = htmlspecialchars($name); // convert name to htmlspecialchars for html exploit patch
	
	// get player info
	$warned_id = name_to_id($db, $name);
	$warned_player = id_to_player($warned_id);
	$player = $socket->get_player();

	// if they're a mod, warn the user
	if($player->group >= 2 && ($warned_player->group < 2 || $player->server_owner == true)){

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
			if (isset($warned_player)) {
				$safe_pname = htmlspecialchars($player->name);
				$player->chat_room->send_chat("systemChat`$safe_pname has given $safe_name $num $w_str. They have been banned from the chat for $time seconds.", $player->user_id);
			}
			else {
				$player->write("message`Error: Could not find a user named \"$safe_name\" on this server.");
			}
		}
	}
	// if they aren't a mod, tell them
	else {
		$player->write("message`Error: You lack the power to warn $safe_name.");
	}
}




//--- ban a player -------------------------------------------------------
function client_ban ($socket, $data) {
	global $db;
	list($banned_name, $seconds, $reason) = explode("`", $data);

	$player = $socket->get_player();
	$banned_id = name_to_id($db, $banned_name);
	$ban_player = id_to_player($banned_id);
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

	if($player->group >= 2 && isset($ban_player)) {
		if(isset($player->chat_room)) {
			$safe_pname = htmlspecialchars($player->name);
			$player->chat_room->send_chat("systemChat`$safe_pname has banned $safe_name for $disp_time. $disp_reason. This ban has been recorded at https://pr2hub.com/bans.", $player->user_id);
		}
		if(isset($ban_player) && $ban_player->group < 2) {
			$ban_player->remove();
		}
	}
}



//--- promote a player to a moderator -------------------------------------
function client_promote_to_moderator ($socket, $data) {
	global $db, $port;
	list($name, $type) = explode("`", $data);
	$from_player = $socket->get_player();
	$to_id = name_to_id($db, $name);
	$to_player = id_to_player($to_id); // define before if
	$safe_name = htmlspecialchars($name); // convert name to htmlspecialchars for html exploit patch

	// if they're an admin, continue with the promotion (1st line of defense)
	if($from_player->group > 2){

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

		if(isset($from_player->chat_room) && (isset($to_player) || $type != 'temporary')) {
			$same_fname = htmlspecialchars($from_player->name);
			$from_player->chat_room->send_chat("systemChat`$safe_fname has promoted $safe_name to a $type moderator! May they reign in $reign_time of peace and prosperity! Make sure you read the moderator guidelines at https://jiggmin2.com/forums/showthread.php?tid=12", $from_player->user_id);
		}
	}
	// if they're not an admin, tell them
	else {
		$from_player->write("message`Error: You lack the power to promote $safe_name to a $type moderator.");
	}
}


//-- demote a moderator ------------------------------------------------------------------
function client_demote_moderator ($socket, $name) {
	$from_player = $socket->get_player();

	if($from_player->group == 3){
		global $port, $db;
		$to_id = name_to_id($db, $name);
		$to_player = id_to_player($to_id);

		demote_mod($port, $name, $from_player, $to_player);
	}
}


?>
