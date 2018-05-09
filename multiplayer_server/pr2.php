<?php

namespace pr2\multi;

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once __DIR__ . '/../common/env.php';
require_once __DIR__ . '/../common/pdo_connect.php';
require_once __DIR__ . '/../common/multi_queries.php';
require_once __DIR__ . '/../vend/socket/index.php';

require_once __DIR__ . '/fns/admin/demod.php';
require_once __DIR__ . '/fns/admin/promote_to_moderator.php';
require_once __DIR__ . '/fns/admin/server_owner.php';
require_once __DIR__ . '/fns/artifact/has_found_artifact.php';
require_once __DIR__ . '/fns/artifact/save_finder.php';
require_once __DIR__ . '/fns/artifact/save_first_finder.php';
require_once __DIR__ . '/fns/misc/data_fns.php';
require_once __DIR__ . '/fns/misc/utils.php';
require_once __DIR__ . '/fns/tournament/announce_tournament.php';
require_once __DIR__ . '/fns/tournament/issue_tournament.php';

require_once __DIR__ . '/client/become_process.php';
require_once __DIR__ . '/client/check_status.php';
require_once __DIR__ . '/client/close.php';
require_once __DIR__ . '/client/ingame.php';
require_once __DIR__ . '/client/lobby.php';
require_once __DIR__ . '/client/moderation.php';
require_once __DIR__ . '/client/ping.php';
require_once __DIR__ . '/client/request_login_id.php';

require_once __DIR__ . '/process/check_status.php';
require_once __DIR__ . '/process/register_login.php';
require_once __DIR__ . '/process/shut_down.php';
require_once __DIR__ . '/process/start_new_day.php';
require_once __DIR__ . '/process/update_cycle.php';
require_once __DIR__ . '/process/vault.php';

require_once __DIR__ . '/Artifact.php';
require_once __DIR__ . '/loadup.php';
require_once __DIR__ . '/Player.php';
require_once __DIR__ . '/PR2SocketServer.php';
require_once __DIR__ . '/PR2Client.php';
require_once __DIR__ . '/CourseBox.php';
require_once __DIR__ . '/ChatMessage.php';
require_once __DIR__ . '/GuildPoints.php';
require_once __DIR__ . '/HappyHour.php';
require_once __DIR__ . '/RaceStats.php';
require_once __DIR__ . '/Hat.php';
require_once __DIR__ . '/LocalBans.php';
require_once __DIR__ . '/LoiterDetector.php';
require_once __DIR__ . '/TemporaryItems.php';
require_once __DIR__ . '/Perks.php';
require_once __DIR__ . '/RankupCalculator.php';

require_once __DIR__ . '/parts/Bodies.php';
require_once __DIR__ . '/parts/Feet.php';
require_once __DIR__ . '/parts/Hats.php';
require_once __DIR__ . '/parts/Heads.php';
require_once __DIR__ . '/parts/Prize.php';
require_once __DIR__ . '/parts/Prizes.php';

require_once __DIR__ . '/rooms/Room.php';
require_once __DIR__ . '/rooms/LevelListRoom.php';
require_once __DIR__ . '/rooms/ChatRoom.php';
require_once __DIR__ . '/rooms/Game.php';
require_once __DIR__ . '/rooms/modes/deathmatch.php';
require_once __DIR__ . '/rooms/modes/eggs.php';
require_once __DIR__ . '/rooms/modes/objective.php';
require_once __DIR__ . '/rooms/modes/race.php';

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
