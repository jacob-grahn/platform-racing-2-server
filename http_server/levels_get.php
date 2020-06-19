<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;

$count = default_get('count', 100);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('levels-get-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // check login
    $user_id = (int) token_login($pdo, true, false, 'g');
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = "Guests can't load or save levels. To access this feature, please create your own account.";
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('levels-get-'.$user_id, 3, 2);

    // get levels
    $levels = levels_select_by_owner($pdo, $user_id);

    // handle special characters
    foreach ($levels as $key => $level) {
        $level->title = utf8_encode($level->title);
        $level->note = utf8_encode($level->note);
        $level->rating = round($level->rating, 2);
        $levels[$key] = $level;
    }
    
    // tell the world
    $ret->success = true;
    $ret->levels = $levels;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
