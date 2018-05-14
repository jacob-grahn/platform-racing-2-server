<?php

// all fns
require_once HTTP_FNS . '/all_fns.php';

// truncate today's login attempts
require_once QUERIES_DIR . '/login_attempts/login_attempts_truncate.php';

// delete old rating entries
require_once QUERIES_DIR . '/ratings/ratings_delete_old.php';

// gp reset
require_once QUERIES_DIR . '/guilds/guilds_reset_gp_today.php';
require_once QUERIES_DIR . '/gp/gp_reset.php';

// expire rank token purchases
require_once QUERIES_DIR . '/rank_token_rentals/rank_token_rentals_delete_old.php';

// expire login tokens
require_once QUERIES_DIR . '/tokens/tokens_delete_old.php';

// expire old guild transfers/email changes
require_once QUERIES_DIR . '/guild_transfers/guild_transfers_expire_old.php';
require_once QUERIES_DIR . '/changing_emails/changing_emails_expire_old.php';

// start the new day
require_once QUERIES_DIR . '/exp_today/exp_today_truncate.php';
require_once QUERIES_DIR . '/servers/servers_select.php';


// tell the command line
$time = date('r');
output("Daily CRON starting at $time...");

// connect
$pdo = pdo_connect();

login_attempts_truncate($pdo);
ratings_delete_old($pdo);
guilds_reset_gp_today($pdo);
gp_reset($pdo);
rank_token_rentals_delete_old($pdo);
tokens_delete_old($pdo);
guild_transfers_expire_old($pdo);
changing_emails_expire_old($pdo);
exp_today_truncate($pdo);
poll_servers(servers_select($pdo), 'start_new_day`');

// tell the command line
output('Daily CRON successful.');
