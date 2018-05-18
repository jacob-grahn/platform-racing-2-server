<?php

namespace pr2\multi;

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once COMMON_DIR . '/multi_queries.php';
require_once SOCKET_DAEMON_FILES;

require_once PR2_FNS_DIR . '/data_fns.php';
require_once PR2_FNS_DIR . '/utils.php';
require_once PR2_FNS_DIR . '/process_fns.php';

require_once PR2_FNS_DIR . '/artifact_fns.php';
require_once PR2_FNS_DIR . '/tournament_fns.php';
require_once PR2_FNS_DIR . '/vault_fns.php';

require_once PR2_FNS_DIR . '/client/client_misc_fns.php';
require_once PR2_FNS_DIR . '/client/ingame.php';
require_once PR2_FNS_DIR . '/client/lobby.php';
require_once PR2_FNS_DIR . '/client/moderation.php';

require_once PR2_FNS_DIR . '/staff/demod.php';
require_once PR2_FNS_DIR . '/staff/promote_to_moderator.php';
require_once PR2_FNS_DIR . '/staff/server_owner.php';

require_once PR2_ROOT . '/Artifact.php';
require_once PR2_ROOT . '/loadup.php';
require_once PR2_ROOT . '/Player.php';
require_once PR2_ROOT . '/PR2SocketServer.php';
require_once PR2_ROOT . '/PR2Client.php';
require_once PR2_ROOT . '/CourseBox.php';
require_once PR2_ROOT . '/ChatMessage.php';
require_once PR2_ROOT . '/GuildPoints.php';
require_once PR2_ROOT . '/HappyHour.php';
require_once PR2_ROOT . '/RaceStats.php';
require_once PR2_ROOT . '/Hat.php';
require_once PR2_ROOT . '/LocalBans.php';
require_once PR2_ROOT . '/LoiterDetector.php';
require_once PR2_ROOT . '/TemporaryItems.php';
require_once PR2_ROOT . '/Perks.php';
require_once PR2_ROOT . '/RankupCalculator.php';

require_once PR2_ROOT . '/parts/Bodies.php';
require_once PR2_ROOT . '/parts/Feet.php';
require_once PR2_ROOT . '/parts/Hats.php';
require_once PR2_ROOT . '/parts/Heads.php';
require_once PR2_ROOT . '/parts/Prize.php';
require_once PR2_ROOT . '/parts/Prizes.php';

require_once PR2_ROOT . '/rooms/Room.php';
require_once PR2_ROOT . '/rooms/LevelListRoom.php';
require_once PR2_ROOT . '/rooms/ChatRoom.php';
require_once PR2_ROOT . '/rooms/Game.php';
require_once PR2_ROOT . '/rooms/modes/deathmatch.php';
require_once PR2_ROOT . '/rooms/modes/eggs.php';
require_once PR2_ROOT . '/rooms/modes/objective.php';
require_once PR2_ROOT . '/rooms/modes/race.php';

output("Initializing startup...");

Prizes::init();
RankupCalculator::init();
HappyHour::$random_hour = rand(0, 36);

$server_id = (int) $argv[1];

$port = 0;
$server_name = '';
$uptime = '';
$guild_id = 0;
$guild_owner = 0;
$server_expire_time = '';
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

$pdo = pdo_connect();

// load in startup info
output('Requesting loadup information...');
begin_loadup($server_id);

// start the socket server
$date = date('r');
output("Starting PR2 server $server_name (ID: #$server_id) on port $port...");
$daemon = new \chabot\SocketDaemon();
$server = $daemon->createServer('\pr2\multi\PR2SocketServer', '\pr2\multi\PR2Client', 0, $port);
output("Success! Server started on $date.");
$daemon->process();
