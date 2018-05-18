<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/levels/levels_select_by_rand.php';

$ip = get_ip();

try {
    // rate limiting
    rate_limit('random-level-'.$ip, 10, 1, "Please wait at least 10 seconds before generating another random level.");

    // connect
    $pdo = pdo_connect();

    // get a random level
    $levels = levels_select_by_rand($pdo);
    echo json_encode($levels);
} catch (Exception $e) {
    $message = $e->getMessage();
    echo "Error: $message";
} finally {
    die();
}
