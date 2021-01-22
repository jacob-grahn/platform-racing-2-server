<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/favorite_levels.php';

$page = (int) default_post('page', 1);
$ip = get_ip();

$page = max(1, min($page, 9));
$cache_expire = 30; // keep results cached for 30 seconds

try {
    // rate limiting
    rate_limit("favorite-levels-view-$ip", 5, 5);

    // check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // connect
    $pdo = pdo_connect();

    // login
    $user_id = (int) token_login($pdo, false, false, 'g');
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = "Guests can't save favorite levels. To access this feature, please create your own account.";
        throw new Exception($e);
    }

    // formats an apcu key by both IP and ID so that people can't maliciously trigger the rate limit via request forging
    $key = "favorite-levels-$ip-$user_id-$page";

    $page_str = apcu_fetch($key);
    while ($page_str === 'WAIT') {
        sleep(1);
        $page_str = apcu_fetch($key);
    }

    if ($page_str === false) {
        rate_limit("favorite-levels-$user_id", 5, 3);
        apcu_add($key, 'WAIT', 5); // will not overwrite existing

        // search
        $levels = favorite_levels_select($pdo, $user_id, $page);
        $page_str = format_level_list($levels);
        apcu_store($key, $page_str, $cache_expire); // will overwrite existing
    }

    echo $page_str;
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
    die(json_encode($ret));
}
