<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/messages.php';

$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check referrer
    require_trusted_ref('delete all of your PMs');

    // rate limiting
    $rl_msg = 'You may only delete all of your PMs once every 15 minutes. Try again later.';
    rate_limit('delete-all-messages-'.$ip, 900, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, false);
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = "Guests can't use the private messaging system. To access this feature, please create your own account.";
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('delete-all-messages-'.$user_id, 900, 1, $rl_msg);

    // delete their PMs
    messages_delete_all($pdo, $user_id);

    // tell the world
    $ret->success = true;
    $ret->message = 'All of your PMs have been deleted!';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
