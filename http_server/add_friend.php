<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/friends.php';

$friend_name = default_post('target_name', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // rate limiting
    rate_limit('friends-list-'.$ip, 3, 2);

    // sanity check: was a name sent?
    if (is_empty($friend_name)) {
        throw new Exception('Who are you trying to befriend?');
    }

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, false);
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = 'Guests can\'t use user lists. To access this feature, please create your own account.';
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('friends-list-'.$user_id, 3, 2);

    // get the new friend's id
    $friend_id = (int) name_to_id($pdo, $friend_name);

    // create the magical one sided friendship
    friend_insert($pdo, $user_id, $friend_id);

    // tell it to the world
    $safe_friend_name = htmlspecialchars($friend_name, ENT_QUOTES);
    $ret->success = true;
    $ret->message = "$safe_friend_name has been added to your friends list!";
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
