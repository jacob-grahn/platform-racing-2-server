<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/servers.php';

$server_id = (int) default_get('server_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // sanity check: was there any value found for the server id?
    if ($server_id === 0) {
        throw new Exception('Invalid server ID specified.');
    }

    // rate limiting
    $rl_msg = 'Please wait at least one minute before attempting to use the super booster again.';
    rate_limit('super-booster-'.$ip, 60, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // get user id
    $user_id = (int) token_login($pdo, false);

    // more rate limiting
    rate_limit('super-booster-'.$user_id, 60, 1, $rl_msg);

    // get server info
    $server = server_select($pdo, $server_id);

    // remember that the super booster was used
    rate_limit('sb-'.$user_id, 86400, 1, 'The Super Booster can only be used once per day.');

    // send a message to the player's server giving them a super boost
    talk_to_server($server->address, $server->port, $server->salt, "unlock_super_booster`$user_id", false);

    // reply
    $ret->success = true;
    $ret->message = 'Super Booster active! You will start your next race with +10 speed, jump, and acceleration.';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
