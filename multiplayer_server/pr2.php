<?php

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once(__DIR__ . '/../env.php');

require_once(__DIR__ . '/fns/DB.php');
require_once(__DIR__ . '/fns/db_fns.php');
require_once(__DIR__ . '/fns/data_fns.php');
require_once(__DIR__ . '/fns/announce_tournament.php');
require_once(__DIR__ . '/fns/issue_tournament.php');
require_once(__DIR__ . '/fns/sort_chat_room_array.php');
require_once(__DIR__ . '/fns/shutdown_server.php');
require_once(__DIR__ . '/fns/utils.php');
require_once(__DIR__ . '/fns/vault.php');

require_once(__DIR__ . '/socket/socket.php');

require_once(__DIR__ . '/client/become_process.php');
require_once(__DIR__ . '/client/check_status.php');
require_once(__DIR__ . '/client/close.php');
require_once(__DIR__ . '/client/ingame.php');
require_once(__DIR__ . '/client/lobby.php');
require_once(__DIR__ . '/client/moderation.php');
require_once(__DIR__ . '/client/ping.php');
require_once(__DIR__ . '/client/request_login_id.php');

require_once(__DIR__ . '/process/check_status.php');
require_once(__DIR__ . '/process/register_login.php');
require_once(__DIR__ . '/process/send_message_to_player.php');
require_once(__DIR__ . '/process/shut_down.php');
require_once(__DIR__ . '/process/start_new_day.php');
require_once(__DIR__ . '/process/unlock_super_booster.php');
require_once(__DIR__ . '/process/update_cycle.php');
require_once(__DIR__ . '/process/vault.php');

require_once(__DIR__ . '/loadup.php');
require_once(__DIR__ . '/socket_server.php');
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

$db;


//load in startup info
output('requesting startup info...');
begin_loadup( $server_id );



//start the socket server
$date = date( 'r' );
output( "Starting pr2 server $server_name on port $port at on $date." );
$daemon = new socketDaemon();
$server = $daemon->create_server('pr2_server', 'pr2_server_client', 0, $port);
$daemon->process();


?>
