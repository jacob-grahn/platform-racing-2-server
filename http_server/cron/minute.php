<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/artifact_locations/artifact_location_select.php';
require_once __DIR__ . '/../queries/messages/messages_select_recent.php';
require_once __DIR__ . '/../queries/bans/bans_select_recent.php';
require_once __DIR__ . '/../queries/levels/levels_select_campaign.php';
require_once __DIR__ . '/../queries/levels/level_increment_play_count.php';
require_once __DIR__ . '/../queries/servers/servers_select.php';
require_once __DIR__ . '/../queries/servers/server_update_status.php';
require_once __DIR__ . '/../queries/users/user_select.php';
require_once __DIR__ . '/../queries/gp/gp_increment.php';
require_once __DIR__ . '/../queries/guilds/guild_increment_gp.php';
require_once __DIR__ . '/minute_fns.php';

output('minute is starting');

$pdo = pdo_connect();

generate_level_list($pdo, 'newest');
run_update_cycle($pdo);
write_server_status($pdo);

echo 'result=ok';
