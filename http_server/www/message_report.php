<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/messages_reported/messages_reported_insert.php';
require_once __DIR__ . '/../queries/messages/message_select.php';

$message_id = (int) $_POST['message_id'];
$time = (int) time();
$ip = get_ip();

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // rate limiting
    rate_limit('message-report-'.$ip, 5, 2, "Please wait at least 5 seconds before trying to report another PM.");
    rate_limit('message-report-'.$ip, 60, 5);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('message-report-'.$user_id, 5, 2, "Please wait at least 5 seconds before trying to report another PM.");
    rate_limit('message-report-'.$user_id, 60, 5);

    // make sure this user is the recipient of this message
    $message = message_select($pdo, $message_id);
    if ($message->to_user_id !== $user_id) {
        throw new Exception('This message was not sent to you.');
    }

    // insert the message into the reported messages table
    messages_reported_insert($pdo, $row->to_user_id, $row->from_user_id, $ip, $row->ip, $row->time, $reported_time, $message_id, $row->message);

    // tell it to the world
    echo 'message=The message was reported successfully!';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
