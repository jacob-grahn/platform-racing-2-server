<?php

//--- loose hat ----------------------------------------------------------------------------
function loose_hat($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->loose_hat($player, $data);
	}
}



//--- pick up a lost hat ----------------------------------------------------------------------------
function get_hat($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->get_hat($player, $data);
	}
}



//--- set pos ------------------------------------------------------------------
function p($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->set_pos($player, $data);
	}
}



//--- set exact pos ----------------------------------------------------------------
function exact_pos($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->set_exact_pos($player, $data);
	}
}



//--- squash another player ---------------------------------------------------------------
function squash($socket, $data) {
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->squash($player, $data);
	}
}



//--- set variable ----------------------------------------------------------------
function set_var($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->set_var($player, $data);
	}
}



//--- add an effect -------------------------------------------------------------
function add_effect($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->send_to_room('addEffect`'.$data, $player->user_id);
	}
}



//---- use a lightning item -------------------------------------------------------
function zap($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->send_to_room('zap`', $player->user_id);
	}
}


//--- hit a block ---------------------------------------------------------------
function hit($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->send_to_room('hit'.$data, $player->user_id);
	}
}



//--- touch a block ---------------------------------------------------------------
function activate($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->send_to_room('activate`'.$data.'`', $player->user_id);
	}
}



//--- bump a heart block ---------------------------------
function heart($socket, $data) {
	$player = $socket->get_player();
	if(isset($player->game_room)) {
		$player->lives++;
		$player->game_room->send_to_room('heart'.$player->temp_id.'`', $player->user_id);
	}
}



//--- finish drawing ------------------------------------------------------------
function finish_drawing($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->finish_drawing($player, $data);
	}
}



//--- finish race ------------------------------------------------------------
function finish_race($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->remote_finish_race($player, $data);
	}
}



//--- quit race -----------------------------------------------------------------
function quit_race($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->quit_race($player);
	}
}


//--- grab egg -----------------------------------------------------------------
function grab_egg($socket, $data){
	$player = $socket->get_player();
	if(isset($player->game_room)){
		$player->game_room->grab_egg( $player, $data );
	}
}



//-- record single finish in objective mode -----------------------------------------------
function objective_reached( $socket, $data ) {
	$player = $socket->get_player();
	if( isset($player->game_room) ) {
		$player->game_room->objective_reached( $player, $data );
	}
}


?>
