<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/ignored.php';
require_once QUERIES_DIR . '/messages.php';

$to_name = default_post('to_name', '');
$message = default_post('message', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // sanity check
    if (is_empty($to_name) || is_empty($message)) {
        throw new Exception('Some data is missing. Make sure you enter a name and a message.');
    }

    // ref check
    require_trusted_ref('send PMs');

    // rate limiting
    rate_limit('send-pm-attempt-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();

    // variables
    $from_user_id = (int) token_login($pdo, false);
    $to_user_id = (int) name_to_id($pdo, $to_name);

    // send it
    send_pm($pdo, $from_user_id, $to_user_id, $message);

    // tell the world
    $ret->success = true;
    $ret->message = 'Your message was sent successfully!';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
