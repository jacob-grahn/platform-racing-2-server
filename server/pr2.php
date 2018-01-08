#!/usr/bin/php
<?php

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once('./fns/DB.php');
require_once('./fns/db_fns.php');
require_once('./fns/data_fns.php');
require_once('./socket/socket.php');
require_once('pr2_admin.php');
require_once('pr2_ingame.php');
require_once('pr2_loadup.php');
require_once('pr2_lobby.php');
require_once('pr2_moderation.php');
require_once('pr2_server.php');
require_once('pr2_server_owner.php');
require_once('pr2_utils.php');
require_once('pr2_vault.php');
require_once('Player.php');
require_once('CourseBox.php');
require_once('ChatMessage.php');
require_once('RaceStats.php');
require_once('Hat.php');
require_once('SimDetector.php');
require_once('LocalBans.php');
require_once('LoiterDetector.php');
require_once('GuildPoints.php');
require_once('TemporaryItems.php');
require_once('Perks.php');
require_once('Robots.php');
require_once('RankupCalculator.php');
require_once('./parts/Bodies.php');
require_once('./parts/Feet.php');
require_once('./parts/Hats.php');
require_once('./parts/Heads.php');
require_once('./parts/Prize.php');
require_once('./parts/Prizes.php');
require_once('./rooms/Room.php');
require_once('./rooms/LevelListRoom.php');
require_once('./rooms/ChatRoom.php');
require_once('./rooms/Game.php');
require_once('./rooms/modes/deathmatch.php');
require_once('./rooms/modes/eggs.php');
require_once('./rooms/modes/objective.php');
require_once('./rooms/modes/race.php');

Prizes::init();
RankupCalculator::init();


$server_id = (int) $argv[1];

$port = 0;
$server_name = '';
$guild_id = 0;
$guild_owner = 0;
$key = '';

$login_array = array();
$game_array = array();
$player_array = array();
$socket_array = array();
$chat_room_array = array();
$campaign_array = array();
$play_count_array = array();

$campaign_room = new LevelListRoom();
$best_room = new LevelListRoom();
$best_today_room = new LevelListRoom();
$newest_room = new LevelListRoom();
$search_room = new LevelListRoom();

$max_players = 200;
$min_version = .60;


//load in startup info
output('requesting startup info...');
begin_loadup( $server_id );



//start the socket server
$date = date( 'r' );
output( "Starting pr2 server $server_name on port $port at on $date." );
$daemon = new socketDaemon();
$server = $daemon->create_server('pr2_server', 'pr2_server_client', 0, $port);
$daemon->process();





function request_login_id($socket, $data) {
	if(!isset($socket->login_id)) {
		global $login_array;
		$socket->login_id = get_login_id();
		$login_array[$socket->login_id] = $socket;
		$socket->write('setLoginID`'.$socket->login_id);
	}
}



function announce_tournament( $chat ) {
	if( pr2_server::$tournament ) {
		$chat->send_to_all('systemChat`Tournament mode is on!<br/>'
			.'Hat: '.Hats::id_to_str(pr2_server::$tournament_hat).'<br/>'
			.'Speed: '.pr2_server::$tournament_speed.'<br/>'
			.'Accel: '.pr2_server::$tournament_acceleration.'<br/>'
			.'Jump: '.pr2_server::$tournament_jumping);
	}
	else {
		$chat->send_to_all('systemChat`Tournament mode is off.');
	}
}












//--- close ------------------------------------------------------------
function close($socket, $data){
	$socket->close();
	$socket->on_disconnect();
}



//--- keep alive ---------------------------------------------------------
function ping($socket, $data){
	$socket->write( 'ping`' . time() );
}



//--- set client version number -------------------------------------------
function version($socket, $data) {
}


//--- shut down -------------
function shutdown_server() {
	global $player_array;
	foreach($player_array as $player){
		$player->write('message`The server is restarting, hold on a sec...');
		$player->remove();
	}
	sleep(1);
	exit();
}





?>
