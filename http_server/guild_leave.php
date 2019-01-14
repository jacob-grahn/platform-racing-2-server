<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;

$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // get and validate referrer
    require_trusted_ref('leave your guild');

    // rate limiting
    rate_limit('guild-leave-attempt-'.$ip, 5, 1);

    // connect to the db
    $pdo = pdo_connect();

    // get their login
    $user_id = (int) token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);

    // sanity check
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }

    // leave the guild
    guild_increment_member($pdo, $account->guild, -1);
    user_update_guild($pdo, $user_id, 0);

    // tell it to the world
    $ret->success = true;
    $ret->message = 'You have left the guild.';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
