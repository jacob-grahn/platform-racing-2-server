<?php

namespace pr2\multi;

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

// env
require_once __DIR__ . '/../config.php';

// ignore travis warnings and define server salt
// phpcs:disable
define('SALT', $COMM_PASS);
// phpcs:enable

// load dependencies
require_once COMMON_DIR . '/multi_queries.php';
require_once SOCKET_DAEMON_FILES;
require_once FNS_DIR . '/common_fns.php';

require_once PR2_FNS . '/loadup_fns.php';
require_once PR2_FNS . '/multi_data_fns.php';
require_once PR2_FNS . '/process_fns.php';
require_once PR2_FNS . '/utils.php';

require_once PR2_FNS . '/artifact_fns.php';
require_once PR2_FNS . '/tournament_fns.php';
require_once PR2_FNS . '/vault_fns.php';

require_once PR2_FNS . '/client/client_misc_fns.php';
require_once PR2_FNS . '/client/ingame.php';
require_once PR2_FNS . '/client/lobby.php';
require_once PR2_FNS . '/client/moderation.php';

require_once PR2_FNS . '/staff/demod.php';
require_once PR2_FNS . '/staff/promote_to_moderator.php';
require_once PR2_FNS . '/staff/server_owner.php';

require_once PR2_ROOT . '/parts/Parts.php';
require_once PR2_ROOT . '/parts/Prizes.php';

require_once PR2_ROOT . '/rooms/Room.php';
require_once PR2_ROOT . '/rooms/LevelListRoom.php';
require_once PR2_ROOT . '/rooms/ChatRoom.php';
require_once PR2_ROOT . '/rooms/Game.php';

require_once PR2_ROOT . '/Artifact.php';
require_once PR2_ROOT . '/CourseBox.php';
require_once PR2_ROOT . '/ChatMessage.php';
require_once PR2_ROOT . '/GuildPoints.php';
require_once PR2_ROOT . '/HappyHour.php';
require_once PR2_ROOT . '/Mutes.php';
require_once PR2_ROOT . '/LoiterDetector.php';
require_once PR2_ROOT . '/Player.php';
require_once PR2_ROOT . '/PR2SocketServer.php';
require_once PR2_ROOT . '/PR2Client.php';
require_once PR2_ROOT . '/RaceStats.php';
require_once PR2_ROOT . '/ServerBans.php';
require_once PR2_ROOT . '/TemporaryItems.php';

// ensure no data is lost to a server crash
register_shutdown_function('__crashHandler');

// output status to console
output("Initializing startup...");

// connect to the db
$reconnect_attempted = false;
try {
    $pdo = pdo_connect();
} catch (Exception $e) {
    output('FATAL ERROR: ' . $e->getMessage());
    die();
}

// server info
$server_id = (int) $argv[1];
$verbose = $argc > 2 && strtolower($argv[2]) === 'true';
$port = 9159;
$server_name = 'bob';
$is_ps = false;
$guild_id = 0;
$guild_owner = 0;
$server_expire_time = 0;
$max_players = 200;

// prizes/random hh hour
Prizes::init();

// important arrays
$login_array = array();
$game_array = array();
$player_array = array();
$socket_array = array();
$chat_room_array = array();
$campaign_array = array();
$level_prize_array = array();
$play_count_array = array();

// rooms
$campaign_room = new LevelListRoom('campaign');
$best_room = new LevelListRoom('best');
$best_week_room = new LevelListRoom('best_week');
$newest_room = new LevelListRoom('newest');
$search_room = new LevelListRoom('search');

// load in startup info
output('Requesting loadup information...');
$uptime = time();

begin_loadup($server_id);

// start the socket server
output("Starting PR2 server $server_name (ID: #$server_id) on port $port...");
$daemon = new \chabot\SocketDaemon();
$server = $daemon->createServer('\pr2\multi\PR2SocketServer', '\pr2\multi\PR2Client', 0, $port);
output("Success! Server started" . ($verbose ? ' (in verbose mode)' : '') . ' on ' . date('r', $uptime));
$daemon->process();
