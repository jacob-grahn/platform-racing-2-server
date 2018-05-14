<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/users/user_select_expanded.php';
require_once QUERIES_DIR . '/users/user_update_guild.php';
require_once QUERIES_DIR . '/guilds/guild_increment_member.php';

$ip = get_ip();

try {
    // get and validate referrer
    require_trusted_ref('leave your guild');

    // rate limiting
    rate_limit('guild-leave-attempt-'.$ip, 5, 1);

    // connect to the db
    $pdo = pdo_connect();

    // get their login
    $user_id = token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);

    // sanity check
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }

    // leave the guild
    guild_increment_member($pdo, $account->guild, -1);
    user_update_guild($pdo, $user_id, 0);

    // tell it to the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = 'You have left the guild.';
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
} finally {
    echo json_encode($reply);
    die();
}
