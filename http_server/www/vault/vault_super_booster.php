<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/servers/server_select.php';

$server_id = (int) $_GET['server_id'];
$ip = get_ip();

try {
    // sanity check: was there any value found for the server id?
    if (is_empty($server_id, false)) { // is_empty($value, false) includes the number 0 as empty.
        throw new Exception('Invalid server ID specified.');
    }

    // rate limiting
    rate_limit(
        'super-booster-'.$ip,
        60,
        1,
        "Please wait at least one minute before attempting to use the super booster again."
    );

    // connect
    $pdo = pdo_connect();

    // get user id
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit(
        'super-booster-'.$user_id,
        60,
        1,
        "Please wait at least one minute before attempting to use the super booster again."
    );

    // get server info
    $server = server_select($pdo, $server_id);

    // remember that the super booster was used
    $key = "sb-$user_id";
    if (apcu_exists($key)) {
        throw new Exception('The Super Booster can only be used once per day.');
    } else {
        $result = apcu_add($key, true, 86400);
        if (!$result) {
            throw new Exception('Could not store usage.');
        }
    }


    // send a message to the player's server giving them a super boost
    talk_to_server($server->address, $server->port, $server->salt, "unlock_super_booster`$user_id", false);


    // reply
    $r = new stdClass();
    $r->success = true;
    $r->message = 'Super Booster active! You will start your next race with +10 speed, +10 jump, and +10 acceleration.';
    echo json_encode($r);
} catch (Exception $e) {
    $r = new stdClass();
    $r->error = $e->getMessage();
    echo json_encode($r);
}
