<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/exp_today/exp_today_truncate.php';
require_once __DIR__ . '/../queries/login_attempts/login_attempts_truncate.php';
require_once __DIR__ . '/../queries/level_backups/level_backups_delete_old.php';
require_once __DIR__ . '/../queries/ratings/ratings_delete_old.php';
require_once __DIR__ . '/../queries/new_levels/new_levels_delete_old.php';
require_once __DIR__ . '/../queries/users_new/users_new_delete_old.php';
require_once __DIR__ . '/../queries/bans/bans_delete_old.php';
require_once __DIR__ . '/../queries/guilds/guilds_reset_gp_today.php';
require_once __DIR__ . '/../queries/gp/gp_reset.php';
require_once __DIR__ . '/../queries/rank_token_rentals/rank_token_rentals_delete_old.php';
require_once __DIR__ . '/../queries/tokens/tokens_delete_old.php';
require_once __DIR__ . '/../queries/servers/servers_select.php';

$pdo = pdo_connect();
$servers = servers_select($pdo);

exp_today_truncate($pdo);
poll_servers($servers, 'start_new_day`');

login_attempts_truncate($pdo);
level_backups_delete_old($pdo);
ratings_delete_old($pdo);
new_levels_delete_old($pdo);
users_new_delete_old($pdo);
bans_delete_old($pdo);
guilds_reset_gp_today($pdo);
gp_reset($pdo);
rank_token_rentals_delete_old($pdo);
tokens_delete_old($pdo);

output('result=ok');

function output($str)
{
    echo "* $str \n";
}
