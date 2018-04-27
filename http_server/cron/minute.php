<?php

// get script fns
require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/cron_fns.php';
require_once __DIR__ . '/../fns/PseudoRandom.php';

// artifact (artifact_hint.txt, send info to servers)
require_once __DIR__ . '/../queries/levels/level_select.php';
require_once __DIR__ . '/../queries/artifact_locations/artifact_location_select.php';

// new PM check for notification
require_once __DIR__ . '/../queries/messages/messages_select_recent.php';

// apply bans
require_once __DIR__ . '/../queries/bans/bans_select_recent.php';

// update campaign if needed
require_once __DIR__ . '/../queries/levels/levels_select_campaign.php';

// update level play counts
require_once __DIR__ . '/../queries/levels/level_increment_play_count.php';

// update active servers
require_once __DIR__ . '/../queries/servers/servers_select.php';
require_once __DIR__ . '/../queries/servers/server_update_status.php';
require_once __DIR__ . '/../queries/servers/server_update_address.php';

// increment gp
require_once __DIR__ . '/../queries/gp/gp_increment.php';
require_once __DIR__ . '/../queries/guilds/guild_increment_gp.php';

// tell the command line
$time = date('r');
output("Minute CRON starting at $time...");

// connect
$pdo = pdo_connect();

// perform minute tasks
generate_level_list($pdo, 'newest');
update_artifact($pdo);
failover_servers($pdo);
run_update_cycle($pdo);
write_server_status($pdo);

// tell the command line
output('Minute CRON successful.');
