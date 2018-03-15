<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';

$target_id = find('userId');
$ip = get_ip();

try {
    // check referrer
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only kick players from a guild from an approved site such as pr2hub.com.");
    }

    // rate limiting
    rate_limit('guild-kick-attempt-'.$ip, 15, 1, 'Please wait at least 15 seconds before attempting to kick another player from your guild.');

    //--- connect to the db
    $db = new DB();
    $pdo = pdo_connect();

    //--- gather info
    $user_id = token_login($pdo, false);
    $account = $db->grab_row('user_select_expanded', array( $user_id ));
    $target_account = $db->grab_row('user_select_expanded', array( $target_id ));
    $guild = $db->grab_row('guild_select', array( $account->guild ));

    //--- sanity check
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }
    if ($guild->owner_id != $user_id) {
        throw new Exception('You are not the owner of this guild.');
    }
    if ($target_account->guild != $account->guild) {
        throw new Exception('They are not in your guild.');
    }
    if ($user_id == $target_id) {
        throw new Exception('Do not kick your self, yo.');
    }
    if (!isset($target_id)) {
        throw new Exception('Who are you trying to kick from your guild?');
    }


    //--- edit guild in db
    $db->call('user_update_guild', array( $target_id, 0 ));
    $db->call('guild_increment_member', array( $guild->guild_id, -1 ));



    //--- tell it to the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = htmlspecialchars($target_account->name).' has been kicked from '.htmlspecialchars($guild->guild_name).'.';
    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}
