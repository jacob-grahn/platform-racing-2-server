<?php

header("Content-type: text/plain");
require_once __DIR__ . '/../fns/all_fns.php';

$ip = get_ip();

try {
    // get and validate referrer
    $ref = check_ref();
    if ($ref !== true && $ref != '') {
        throw new Exception('It looks like you\'re using PR2 from a third-party website. For security reasons, you may only leave a guild from an approved site such as pr2hub.com.');
    }

    // rate limiting
    rate_limit('guild-leave-attempt-'.$ip, 5, 1);

    // connect to the db
    $db = new DB();
    $pdo = pdo_connect();


    // get their login
    $user_id = token_login($pdo, false);
    $account = $db->grab_row('user_select_expanded', array($user_id));


    // sanity check
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }


    // leave the guild
    $db->call('guild_increment_member', array( $account->guild, -1 ));
    $db->call('user_update_guild', array( $user_id, 0 ));


    // tell it to the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = 'You have left the guild.';
    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}
