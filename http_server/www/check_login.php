<?php

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/users/user_select.php';

header("Content-type: text/plain");

$ip = get_ip();
$ret = new stdClass();
$ret->user_name = "";
$ret->guild_id = 0;

try {
    // rate limiting
    rate_limit('check-login-'.$ip, 10, 1);

    // connect to the db
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo);

    // get their username
    $user = user_select($pdo, $user_id);

    // sanity check: guest account?
    if ($user->power == 0) {
        throw new Exception('You are logged in as a guest.');
    }

    // tell it to the world
    $ret->user_name = $user->name;
    $ret->guild_id = $user->guild;
} catch (Exception $e) {
    unset($e);
} finally {
    die(json_encode($ret));
}
