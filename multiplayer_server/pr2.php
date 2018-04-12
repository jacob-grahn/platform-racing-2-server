<?php

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../http_server/fns/pdo_connect.php';
require_once __DIR__ . '/../vend/socket/index.php';

require_once __DIR__ . '/../http_server/queries/staff/actions/admin_action_insert.php';
require_once __DIR__ . '/../http_server/queries/staff/actions/mod_action_insert.php';
require_once __DIR__ . '/../http_server/queries/pr2/pr2_update.php';
require_once __DIR__ . '/../http_server/queries/epic_upgrades/epic_upgrades_upsert.php';
require_once __DIR__ . '/../http_server/queries/users/user_update_status.php';
require_once __DIR__ . '/../http_server/queries/rank_tokens/rank_token_update.php';
require_once __DIR__ . '/../http_server/queries/exp_today/exp_today_add.php';

require_once __DIR__ . '/fns/artifact/save_finder.php';
require_once __DIR__ . '/fns/data_fns.php';
require_once __DIR__ . '/fns/announce_tournament.php';
require_once __DIR__ . '/fns/issue_tournament.php';
require_once __DIR__ . '/fns/sort_chat_room_array.php';
require_once __DIR__ . '/fns/server_owner.php';
require_once __DIR__ . '/fns/shutdown_server.php';
require_once __DIR__ . '/fns/utils.php';
require_once __DIR__ . '/fns/vault.php';

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
require_once __DIR__ . '/process/send_message_to_player.php';
require_once __DIR__ . '/process/shut_down.php';
require_once __DIR__ . '/process/start_new_day.php';
require_once __DIR__ . '/process/unlock_super_booster.php';
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

\pr2\multi\Prizes::init();
\pr2\multi\RankupCalculator::init();


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

$campaign_room = new \pr2\multi\LevelListRoom();
$best_room = new \pr2\multi\LevelListRoom();
$best_today_room = new \pr2\multi\LevelListRoom();
$newest_room = new \pr2\multi\LevelListRoom();
$search_room = new \pr2\multi\LevelListRoom();

$max_players = 200;
$min_version = .60;

$pdo = pdo_connect();


//load in startup info
output('requesting startup info...');
begin_loadup($server_id);



//start the socket server
$date = date('r');
output("Starting PR2 server $server_name on port $port at on $date.");
$daemon = new \chabot\SocketDaemon();
$server = $daemon->createServer('\pr2\multi\PR2SocketServer', '\pr2\multi\PR2Client', 0, $port);
$daemon->process();
