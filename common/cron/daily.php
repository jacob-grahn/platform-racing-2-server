<?php

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/changing_emails.php';
require_once QUERIES_DIR . '/exp_today.php';
require_once QUERIES_DIR . '/gp.php';
require_once QUERIES_DIR . '/guild_transfers.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';
require_once QUERIES_DIR . '/ratings.php';
require_once QUERIES_DIR . '/servers.php';

// tell the command line
$time = date('r');
output("Daily CRON starting at $time...");

// connect
$pdo = pdo_connect();

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
