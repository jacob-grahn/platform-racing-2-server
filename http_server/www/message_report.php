<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/messages_reported.php';

$message_id = (int) default_post('message_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // sanity check
    if ($message_id <= 0) {
        throw new Exception('Invalid message specified.');
    }

    // rate limiting
    rate_limit('message-report-'.$ip, 5, 2, "Please wait at least 5 seconds before trying to report another PM.");
    rate_limit('message-report-'.$ip, 60, 5);

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
    rate_limit('message-report-'.$user_id, 5, 2, "Please wait at least 5 seconds before trying to report another PM.");
    rate_limit('message-report-'.$user_id, 60, 5);

    // check if the message was already reported
    if (messages_reported_check_existing($pdo, $message_id) === true) {
        throw new Exception("It seems like you've already reported this message.");
    }

    // make sure the message exists and that this user is the recipient of the message
    $message = message_select($pdo, $message_id, true);
    if ($message === false) {
        throw new Exception("The message you tried to report ($message_id) doesn't exist.");
    }
    if ($message->to_user_id != $user_id) {
        throw new Exception('This message was not sent to you.');
    }

    // insert the message into the reported messages table
    $to_id = (int) $message->to_user_id;
    $from_id = (int) $message->from_user_id;
    $msg = $message->message;
    messages_reported_insert($pdo, $to_id, $from_id, $ip, $message->ip, $message->time, time(), $message_id, $msg);

    // tell it to the world
    $ret->success = true;
    $ret->message = 'The message was reported successfully!';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
