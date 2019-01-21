<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/artifact_location.php';

$x = (int) default_post('x', 0);
$y = (int) default_post('y', 0);
$level_id = (int) default_post('level_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // sanity check: is data missing?
    if (is_empty($x, false) || is_empty($y, false) || is_empty($level_id, false)) {
        throw new Exception('Some data is missing.');
    }

    // check referrer
    if (!is_trusted_ref()) {
        throw new Exception("Incorrect referrer.");
    }

    // rate limiting
    $rl_msg = 'Please wait at least 30 seconds before trying to set a new artifact location again.';
    rate_limit('place-artifact-attempt-'.$ip, 30, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo);

    // more rate limiting
    $rl_msg = 'The artifact can only be placed a maximum of 10 times per hour. Try again later.';
    rate_limit('place-artifact-'.$ip, 3600, 10, $rl_msg);
    rate_limit('place-artifact-'.$user_id, 3600, 10, $rl_msg);

    // sanity check: are they Fred?
    if ($user_id !== FRED) {
        throw new Exception('You are not Fred.');
    }

    // update the artifact location in the database
    artifact_location_update($pdo, $level_id, $x, $y);

    // tell the world
    $ret->success = true;
    $ret->message = "Great success! The artifact location will be updated at the top of the next minute.";
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
