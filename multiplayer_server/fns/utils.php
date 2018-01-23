<?php

function send_to_all_players( $str ) {
	global $player_array;
	foreach( $player_array as $player ) {
		$player->write( $str );
	}
}


function send_to_guild( $guild_id, $str ) {
	global $player_array;
	foreach( $player_array as $player ) {
		if( $player->guild_id == $guild_id ) {
			$player->write( $str );
		}
	}
}


//--- takes an id and returns a player ---------------------------------------
function id_to_player($id, $throw_exception=true){
	global $player_array;
	$player = @$player_array[$id];
	if(!isset($player) && $throw_exception){
		throw new Exception('Player id does not exist.');
	}
	return $player;
}



//--- takes a name and returns a player -----------------------------------------
function name_to_player($name){
	global $player_array;
	$return_player = NULL;
	foreach($player_array as $player){
		if($player->name == $name){
			$return_player = $player;
			break;
		}
	}
	return $return_player;
}



//--- checks to see if an ip is banned ------------------------------------------
function get_banned_ip($ip){
	global $banned_ip_array;
	$banned_ip = @$banned_ip_array[$ip];
	if(isset($banned_ip)){
		if($banned_ip->time > time()){
			return $banned_ip;
		}else{
			$banned_ip->remove();
			return false;
		}
	}else{
		return false;
	}
}


//--- get dat dar login id --------------------------------------------------
function get_login_id(){
	static $cur_login_id = 0;
	$cur_login_id++;
	return $cur_login_id;
}


//--- get an existing chat room, or make a new one ---------------------------------------
function get_chat_room($chat_room_name){
	global $chat_room_array;
	if(isset($chat_room_array[$chat_room_name])){
		return $chat_room_array[$chat_room_name];
	}
	else{
		$chat_room = new ChatRoom($chat_room_name);
		return $chat_room;
	}
}


//--- accept bans from other servers ----------------------------------------------------
function apply_bans( $bans ){
	global $player_array;
	
	foreach( $bans as $ban ) {
		foreach( $player_array as $player ){
			if($player->ip == $ban->banned_ip || $player->user_id == $ban->banned_user_id) {
				$player->remove();
			}
		}
	}
}



//--- send new pm notifications out ----------------------------------------------------
function pm_notify( $pms ){
	global $player_array;

	foreach($pms as $pm) {
		if( isset( $player_array[ $pm->to_user_id ] ) ) {
			$player = $player_array[ $pm->to_user_id ];
			$player->write('pmNotify`' . $pm->message_id);
		}
	}
}



//---
function place_artifact( $artifact ) {
	output( "place_artifact: " . json_encode( $artifact ) );
	Game::$artifact_level_id = $artifact->level_id;
	Game::$artifact_x = $artifact->x;
	Game::$artifact_y = $artifact->y;
	$time = strtotime($artifact->updated_time);
	if($time > Game::$artifact_updated_time) {
		Game::$artifact_updated_time = $time;
		reset_artifacts();
	}
}


//---
function reset_artifacts() {
	output('reset_artifacts');
	global $player_array;
	foreach( $player_array as $player ){
		$player->artifact = 0;
	}
}



//--- get the plays we've been holding ------------------------------------------
function drain_plays(){
	global $play_count_array;
	$cup = array();
	
	foreach($play_count_array as $course => $plays){
		$cup[ $course ] = $plays;
	}
	
	$play_count_array = array();
	
	return $cup;
}



//--- get population
function get_population() {
	global $player_array;
	$pop = count($player_array);
	return $pop;
}



//--- get status
function get_status() {
	global $player_array;
	global $max_players;
	$status = 'open';
	if( count($player_array) >= $max_players ) {
		$status = 'full';
	}
	return $status;
}


function output($str) {
	echo "* $str\n";
}

?>
