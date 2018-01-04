<?php


//--- kick a player -------------------------------------------------------------
function kick($socket, $data){
	$name = $data;
	
	$player = $socket->get_player();
	
	if($player->group >= 2){
		
		LocalBans::add($name);
	
		$kicked_player = name_to_player($name);
		if( isset($kicked_player) ) {
			$kicked_player->remove();
		}
	}
}



//--- warn a player -------------------------------------------------------------
function warn($socket, $data){
	list($name, $num) = explode("`", $data);
	
	$player = $socket->get_player();
	
	if($player->group >= 2){
	
		$warned_player = name_to_player($name);	
		
		$s_str = '';
		$time = 0;
		
		if($num == 1){
			$time = 15;
		}
		else if($num == 2){
			$s_str = 's';
			$time = 30;
		}
		else if($num == 3){
			$s_str = 's';
			$time = 60;
		}
	
		if(isset($warned_player) && $warned_player->group < 2){
			$warned_player->chat_ban = time() + $time;
		}	
		
		if(isset($player->chat_room)){
			$player->chat_room->send_chat('systemChat`'.$player->name
			.' has given '.$name.' '.$num.' warning'.$s_str.'. '
			.'They have been banned from the chat for '.$time.' seconds.', $player->user_id);
		}	
	}
}




//--- ban a player -------------------------------------------------------
function ban($socket, $data){
	list($banned_name, $seconds, $reason) = explode("`", $data);
	
	$player = $socket->get_player();
	$ban_player = name_to_player($banned_name);
	$mod_id = $player->user_id;
	
	if($seconds > 60 && $player->temp_mod){
		$seconds = 60;
	}

	if($reason == ''){
		$reason = 'No reason was given.';
	}
	
	if(isset($ban_player) && $player->group > $ban_player->group){
		if(isset($player->chat_room)) {
			$player->chat_room->send_chat('systemChat`'.$player->name
			.' has banned '.$banned_name.' for '.$seconds.' seconds. Reason: '.$reason.'. This ban has been recorded at http://pr2hub.com/bans.', $player->user_id);
		}
		$ban_player->remove();
	}
}



//--- promote a player to a moderator -------------------------------------
function promote_to_moderator($socket, $data){
	list($name, $type) = explode("`", $data);
	$from_player = $socket->get_player();
	if($from_player->group > 2){
		
		$to_player = name_to_player($name);
		if(isset($to_player)){
			if($type == 'temporary') {
				$to_player->become_temp_mod();
			}
			else {
				$to_player->group = 2;
				$to_player->write('setGroup`2');
			}
		}
		
		if($type == 'permanent' || $type == 'trial') {
			global $port;
			global $import_path;
			$safe_name = escapeshellarg($name);
			exec("nohup php $import_path/commands/promote_to_moderator.php $port $safe_name $type > /dev/null &");
		}
		
		if(isset($from_player->chat_room)){
			$from_player->chat_room->send_chat('systemChat`'.$from_player->name
			.' has promoted '.$name
			.' to a '.$type.' moderator! May they reign in 1000 years of peace and prosperity! Make sure you read the moderator guidelines at jiggmin.com/threads/75837-Temporary-Moderator-Guidelines', $from_player->user_id);
		}
	}
}


//-- demote a moderator ------------------------------------------------------------------
function demote_moderator($socket, $name){
	$from_player = $socket->get_player();
	
	if($from_player->group == 3){
		$to_player = name_to_player($name);
		if(isset($to_player) && $to_player->group == 2) {
			$to_player->group = 1;
			$to_player->write('setGroup`1');
		}
		global $port;
		global $import_path;
		$safe_name = escapeshellarg($name);
		exec("nohup php $import_path/commands/demod.php $port $safe_name > /dev/null &");
	}
}


//--- ban yourself ---
function ban_socket($socket) {
	$player = $socket->get_player();
	global $import_path;
	exec('nohup php '.$import_path.'/commands/ban.php '.escapeshellarg($player->user_id).' '.escapeshellarg($socket->remote_address).' '.escapeshellarg($player->name).' > /dev/null &');
	$socket->close();
	$socket->on_disconnect();
}


?>
