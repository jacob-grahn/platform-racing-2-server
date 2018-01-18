<?php

require_once(__DIR__ . '/../fns/all_fns.php');

$db = new DB();

$db->call('exp_today_truncate');
poll_servers_2($db, 'start_new_day`');

$db->call('login_attempts_clean');
$db->call('level_backups_clean');
$db->call('ratings_delete_old');
$db->call('new_levels_clean');
$db->call('users_new_clean');
$db->call('bans_clean');
$db->call('lux_truncate');
$db->call('fred_randomize_rank');
$db->call('guilds_reset_gp_today');
$db->call('gp_daily_reset');
$db->call('rank_token_rentals_delete_expired');
$db->call('tokens_delete_old');

output( 'result=ok' );

function output( $str ) {
	echo "* $str \n";
}

?>
