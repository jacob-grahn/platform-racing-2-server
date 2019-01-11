<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;

$ip = get_ip();

try {
    // rate limiting
    rate_limit('random-level-'.$ip, 10, 1, "Please wait at least 10 seconds before generating another random level.");

    // connect
    $pdo = pdo_connect();

    // get a random level
    $ret = level_select_by_rand($pdo);
    $ret->success = true;
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
