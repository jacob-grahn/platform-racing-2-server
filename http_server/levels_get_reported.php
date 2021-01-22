<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/levels_reported.php';

$count = (int) default_post('count', 100);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('levels-get-reported-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // check if logged in and a moderator
    $user_id = (int) token_login($pdo, true, false, 'g');
    if (check_moderator($pdo, $user_id)->trial_mod) {
        throw new Exception('You lack the power to access this resource.');
    }

    // more rate limiting
    rate_limit('levels-get-reported-'.$user_id, 5, 2);

    // get levels
    $levels = levels_reported_select_unarchived_recent($pdo);

    // tell the world
    $ret->success = true;
    $ret->levels = $levels;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
