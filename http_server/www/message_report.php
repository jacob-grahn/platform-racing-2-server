<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/messages_reported/messages_reported_check_existing.php';
require_once QUERIES_DIR . '/messages_reported/messages_reported_insert.php';
require_once QUERIES_DIR . '/messages/message_select.php';

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
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception(
            "Guests can't use the private messaging system. ".
            "To access this feature, please create your own account."
        );
    }

    // more rate limiting
    rate_limit('message-report-'.$user_id, 5, 2, "Please wait at least 5 seconds before trying to report another PM.");
    rate_limit('message-report-'.$user_id, 60, 5);

    // check if the message was already reported
    $repeat = messages_reported_check_existing($pdo, $message_id);
    if ($repeat === true) {
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
    messages_reported_insert(
        $pdo,
        $message->to_user_id,
        $message->from_user_id,
        $ip,
        $message->ip,
        $message->time,
        time(),
        $message_id,
        $message->message
    );

    // tell it to the world
    echo 'message=The message was reported successfully!';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
