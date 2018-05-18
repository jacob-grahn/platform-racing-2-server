<?php

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/users/user_select.php';

header("Content-type: text/plain");
$ip = get_ip();

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
    echo 'user_name='.urlencode($user->name).'&guild_id='.urlencode($user->guild);
} catch (Exception $e) {
    echo 'user_name=';
} finally {
    die();
}
