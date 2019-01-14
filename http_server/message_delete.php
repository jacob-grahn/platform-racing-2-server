<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/messages.php';

$message_id = (int) default_post('message_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // sanity check
    if ($message_id <= 0) {
        throw new Exception('Invalid message specified.');
    }
    
    // referrer check
    require_trusted_ref('delete PMs');

    // rate limiting
    rate_limit('message-delete'.$ip, 5, 2, "Please wait at least 5 seconds before trying to delete another PM.");

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo);
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = "Guests can't use the private messaging system. To access this feature, please create your own account.";
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('message-delete'.$user_id, 5, 2, "Please wait at least 5 seconds before trying to delete another PM.");

    // delete the message from the database
    message_delete($pdo, $user_id, $message_id);

    // tell the world
    $ret->success = true;
} catch (Exception $e) {
    $ret->success = false;
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
