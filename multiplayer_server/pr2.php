<?php

namespace pr2\multi;

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../http_server/fns/pdo_connect.php';
require_once __DIR__ . '/../vend/socket/index.php';

require_once __DIR__ . '/../http_server/queries/artifact_locations/artifact_location_select.php';
require_once __DIR__ . '/../http_server/queries/artifact_locations/artifact_location_update_first_finder.php';
require_once __DIR__ . '/../http_server/queries/artifacts_found/artifacts_found_select_time.php';
require_once __DIR__ . '/../http_server/queries/artifacts_found/artifacts_found_insert.php';
require_once __DIR__ . '/../http_server/queries/campaign/campaign_select.php';
require_once __DIR__ . '/../http_server/queries/epic_upgrades/epic_upgrades_upsert.php';
require_once __DIR__ . '/../http_server/queries/exp_today/exp_today_add.php';
require_once __DIR__ . '/../http_server/queries/guilds/guild_select.php';
require_once __DIR__ . '/../http_server/queries/messages/message_insert.php';
require_once __DIR__ . '/../http_server/queries/mod_powers/mod_power_delete.php';
require_once __DIR__ . '/../http_server/queries/mod_powers/mod_power_insert.php';
require_once __DIR__ . '/../http_server/queries/pr2/pr2_update.php';
require_once __DIR__ . '/../http_server/queries/purchases/purchases_select_recent.php';
require_once __DIR__ . '/../http_server/queries/promotion_logs/promotion_log_count.php';
require_once __DIR__ . '/../http_server/queries/promotion_logs/promotion_log_insert.php';
require_once __DIR__ . '/../http_server/queries/rank_tokens/rank_token_update.php';
require_once __DIR__ . '/../http_server/queries/staff/actions/admin_action_insert.php';
require_once __DIR__ . '/../http_server/queries/staff/actions/mod_action_insert.php';
require_once __DIR__ . '/../http_server/queries/staff/actions/admin_action_insert.php';
require_once __DIR__ . '/../http_server/queries/servers/server_select.php';
require_once __DIR__ . '/../http_server/queries/users/user_update_status.php';
require_once __DIR__ . '/../http_server/queries/users/user_select.php';
require_once __DIR__ . '/../http_server/queries/users/user_select_by_name.php';
require_once __DIR__ . '/../http_server/queries/users/user_update_power.php';

require_once __DIR__ . '/fns/artifact/has_found_artifact.php';
require_once __DIR__ . '/fns/artifact/save_finder.php';
require_once __DIR__ . '/fns/artifact/save_first_finder.php';

require_once __DIR__ . '/fns/announce_tournament.php';
require_once __DIR__ . '/fns/data_fns.php';
require_once __DIR__ . '/fns/demod.php';
require_once __DIR__ . '/fns/issue_tournament.php';
require_once __DIR__ . '/fns/promote_to_moderator.php';
require_once __DIR__ . '/fns/server_owner.php';
require_once __DIR__ . '/fns/shutdown_server.php';
require_once __DIR__ . '/fns/sort_chat_room_array.php';
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
output("Starting PR2 server $server_name (ID: #$server_id) on port $port...");
$daemon = new \chabot\SocketDaemon();
$server = $daemon->createServer('\pr2\multi\PR2SocketServer', '\pr2\multi\PR2Client', 0, $port);
$daemon->process();

// tell the world
$date = date('r');
output("Success! PR2 server $server_name started on port $port on $date.");
