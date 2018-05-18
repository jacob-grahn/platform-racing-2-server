<?php

// get script fns
require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/cron/cron_fns.php';
require_once HTTP_FNS . '/rand_crypt/PseudoRandom.php';

// artifact (artifact_hint.txt, send info to servers)
require_once QUERIES_DIR . '/levels/level_select.php';
require_once QUERIES_DIR . '/artifact_locations/artifact_location_select.php';

// new PM check for notification
require_once QUERIES_DIR . '/messages/messages_select_recent.php';

// apply bans
require_once QUERIES_DIR . '/bans/bans_select_recent.php';

// update campaign if needed
require_once QUERIES_DIR . '/levels/levels_select_campaign.php';

// update level play counts
require_once QUERIES_DIR . '/levels/level_increment_play_count.php';

// update active servers
require_once QUERIES_DIR . '/servers/servers_select.php';
require_once QUERIES_DIR . '/servers/server_update_status.php';
require_once QUERIES_DIR . '/servers/server_update_address.php';

// increment gp
require_once QUERIES_DIR . '/gp/gp_increment.php';
require_once QUERIES_DIR . '/guilds/guild_increment_gp.php';

// tell the command line
$time = date('r');
output("Minute CRON starting at $time...");

// connect
$pdo = pdo_connect();

// perform minute tasks
//failover_servers($pdo);
check_servers($pdo);
generate_level_list($pdo, 'newest');
//update_artifact($pdo);
run_update_cycle($pdo);
write_server_status($pdo);

// tell the command line
output('Minute CRON successful.');
