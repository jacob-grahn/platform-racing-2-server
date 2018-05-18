<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/levels/levels_select_by_owner.php';

$count = find_no_cookie('count', 100);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('get-levels-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // check login
    $user_id = token_login($pdo);
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception(
            "Guests can't load or save levels. ".
            "To access this feature, please create your own account."
        );
    }

    // more rate limiting
    rate_limit('get-levels-'.$user_id, 3, 2);

    // get levels
    $levels = levels_select_by_owner($pdo, $user_id);
    $str = format_level_list($levels, $count);

    // tell the world
    echo $str;
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
