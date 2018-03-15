<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';

$count = find_no_cookie('count', 100);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('get-levels-'.$ip, 3, 2);

    // connect
    $db = new DB();

    // check login
    $user_id = token_login($db);

    // more rate limiting
    rate_limit('get-levels-'.$user_id, 3, 2);

    // get levels
    $levels = $db->to_array( $db->call('levels_select_by_owner', array($user_id)) );
    $str = format_level_list($levels, $count);

    // tell the world
    echo $str;
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
