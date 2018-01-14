#!/usr/bin/php
<?php

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

<<<<<<< HEAD
$import_path = '/home/jiggmin/pr2/server';

require_once($import_path.'/fns/DB.php');
require_once($import_path.'/fns/db_fns.php');
require_once($import_path.'/fns/data_fns.php');
require_once($import_path.'/socket/socket.php');
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
require_once($import_path.'/parts/Bodies.php');
require_once($import_path.'/parts/Feet.php');
require_once($import_path.'/parts/Hats.php');
require_once($import_path.'/parts/Heads.php');
require_once($import_path.'/parts/Prize.php');
require_once($import_path.'/parts/Prizes.php');
require_once($import_path.'/rooms/Room.php');
require_once($import_path.'/rooms/LevelListRoom.php');
require_once($import_path.'/rooms/ChatRoom.php');
require_once($import_path.'/rooms/Game.php');
require_once($import_path.'/rooms/modes/deathmatch.php');
require_once($import_path.'/rooms/modes/eggs.php');
require_once($import_path.'/rooms/modes/objective.php');
require_once($import_path.'/rooms/modes/race.php');
=======
require_once(__DIR__ . '/../env.php');
require_once(__DIR__ . '/fns/DB.php');
require_once(__DIR__ . '/fns/db_fns.php');
require_once(__DIR__ . '/fns/data_fns.php');
require_once(__DIR__ . '/socket/socket.php');
require_once(__DIR__ . '/pr2_admin.php');
require_once(__DIR__ . '/pr2_ingame.php');
require_once(__DIR__ . '/pr2_loadup.php');
require_once(__DIR__ . '/pr2_lobby.php');
require_once(__DIR__ . '/pr2_moderation.php');
require_once(__DIR__ . '/pr2_server.php');
require_once(__DIR__ . '/pr2_server_owner.php');
require_once(__DIR__ . '/pr2_utils.php');
require_once(__DIR__ . '/pr2_vault.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/CourseBox.php');
require_once(__DIR__ . '/ChatMessage.php');
require_once(__DIR__ . '/RaceStats.php');
require_once(__DIR__ . '/Hat.php');
require_once(__DIR__ . '/SimDetector.php');
require_once(__DIR__ . '/LocalBans.php');
require_once(__DIR__ . '/LoiterDetector.php');
require_once(__DIR__ . '/GuildPoints.php');
require_once(__DIR__ . '/TemporaryItems.php');
require_once(__DIR__ . '/Perks.php');
require_once(__DIR__ . '/Robots.php');
require_once(__DIR__ . '/RankupCalculator.php');
require_once(__DIR__ . '/parts/Bodies.php');
require_once(__DIR__ . '/parts/Feet.php');
require_once(__DIR__ . '/parts/Hats.php');
require_once(__DIR__ . '/parts/Heads.php');
require_once(__DIR__ . '/parts/Prize.php');
require_once(__DIR__ . '/parts/Prizes.php');
require_once(__DIR__ . '/rooms/Room.php');
require_once(__DIR__ . '/rooms/LevelListRoom.php');
require_once(__DIR__ . '/rooms/ChatRoom.php');
require_once(__DIR__ . '/rooms/Game.php');
require_once(__DIR__ . '/rooms/modes/deathmatch.php');
require_once(__DIR__ . '/rooms/modes/eggs.php');
require_once(__DIR__ . '/rooms/modes/objective.php');
require_once(__DIR__ . '/rooms/modes/race.php');
>>>>>>> shell-fix-prodemote

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





<<<<<<< HEAD
?>
=======
?>
>>>>>>> shell-fix-prodemote
