<?php

//--- set right room ----------------------------------------------------
function client_set_right_room ($socket, $data) {
	$player = $socket->get_player();
	if(isset($player->right_room)){
		$player->right_room->remove_player($player);
	}
	if($data != 'none' && isset($player->game_room)) {
		$player->game_room->remove_player($player);
	}
	if($data != 'none' && strpos($data, '`') === false){
		global ${$data.'_room'};
		if(${$data.'_room'} != NULL) {
			${$data.'_room'}->add_player($player);
		}
	}
}



//--- set the chat room -----------------------------------------------
function client_set_chat_room ($socket, $data) {
	$player = $socket->get_player();
	if(isset($player->chat_room)){
		$player->chat_room->remove_player($player);
	}
	if(($data == 'mod' && $player->group < 2) || $data == 'admin' && $player->group < 3){
		$data = 'none';
		$player->write('message`You lack the power to enter this room.');
	}

	if(is_obscene($data)){
		$data = 'none';
		$player->write('message`Keep the room names clean, pretty please. :)');
	}
	if($data != 'none'){
		$chat_room = get_chat_room($data);
		$chat_room->add_player($player);
	}
}



//--- set game room ------------------------------------------------------
function client_set_game_room ($socket, $data) {
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->remove_player($player);
	}
}



//--- join a slot in a course box ------------------------------------------------
function client_fill_slot ($socket, $data) {
	list($course_id, $slot) = explode('`', $data);
	$player = $socket->get_player();
	if(isset($player->right_room)){
		$player->right_room->fill_slot($player, $course_id, $slot);
	}
}



//--- confirm slot --------------------------------------------------------------------
function client_confirm_slot ($socket, $data) {
	$player = $socket->get_player();
	$course_box = $player->course_box;
	if(isset($course_box)){
		$course_box->confirm_slot($player);
	}
}



//--- clear slot --------------------------------------------------------------------
function client_clear_slot ($socket, $data) {
	$player = $socket->get_player();
	$course_box = $player->course_box;
	if(isset($course_box)){
		$course_box->clear_slot($player);
	}
}



//--- force the players who have not confirmed out so the rest can play -----------------
function client_force_start ($socket, $data) {
	$player = $socket->get_player();
	$course_box = $player->course_box;
	if(isset($course_box)){
		$course_box->force_start();
	}
}



//--- returns info for the customize page -----------------------------------------------
function client_get_customize_info ($socket, $data) {
	$player = $socket->get_player();
	$player->send_customize_info();
}



//--- sets info for the character --------------------------------------------------------
function client_set_customize_info ($socket, $data) {
	$player = $socket->get_player();
	$player->set_customize_info($data);
}



//--- chat ----------------------------------------------------------------
function client_chat ($socket, $data) {
	$player = $socket->get_player();
	$player->send_chat($data);
}



//--- get a list of the players that are online ---------------------------------------------
function client_get_online_list ($socket, $data) {
	global $player_array;
	foreach($player_array as $player){
		$socket->write('addUser`'.$player->name.'`'.$player->group.'`'.$player->active_rank.'`'.(count($player->hat_array)-1));
	}
}



//--- get a list of the top chat rooms ----------------------------------------------------------
function client_get_chat_rooms ($socket, $data) {
	global $chat_room_array;
	$temp_array = array_merge($chat_room_array);
	usort($temp_array, 'sort_chat_room_array');

	$str = 'setChatRoomList';
	$count = count($temp_array);
	if($count > 8){
		$count = 8;
	}

	for($i=0; $i<$count; $i++){
		$chat_room = $temp_array[$i];
		$str .= '`'.$chat_room->chat_room_name.' - '.count($chat_room->player_array).' online';
	}

	$socket->write($str);
}



//--- add a user to your ignored array ----------------------------------------------------------
function client_ignore_user ($socket, $data) {
	$player = $socket->get_player();
	$ignored_player = name_to_player($data);
	if(isset($ignored_player)){
		array_push($player->ignored_array, $ignored_player->user_id);
	}
}



//--- remove a user from your ignored array ----------------------------------------------------------
function client_un_ignore_user ($socket, $data) {
	$player = $socket->get_player();
	$ignored_player = name_to_player($data);
	if(isset($player)){
		$index = @array_search($ignored_player->user_id, $player->ignored_array);
		if($index !== false){
			$player->ignored_array[$index] = NULL;
			unset($player->ignored_array[$index]);
		}
	}
}



//-- award kong outfit -------------------------------------------------------------------
function client_award_kong_outfit ($socket, $data) {
	$player = $socket->get_player();
	$player->award_kong_outfit();
}



//-- use a rank token -------------------------------------------------------------------
function client_use_rank_token ($socket, $data) {
	$player = $socket->get_player();
	$player->use_rank_token();
}



//-- un-use a rank token ----------------------------------------------------------------
function client_unuse_rank_token ($socket, $data) {
	$player = $socket->get_player();
	$player->unuse_rank_token();
}

?>
