<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pr2/pr2_fns.php';

$to_name = $_POST['to_name'];
$message = $_POST['message'];
$ip = get_ip();

$ret = new stdClass();
$ret->success = true;

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // ref check
    require_trusted_ref('send PMs');

    // rate limiting
    rate_limit('send-pm-attempt-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();

    // variables
    $from_user_id = token_login($pdo, false);
    $to_user_id = name_to_id($pdo, $to_name);

    // send it
    send_pm($pdo, $from_user_id, $to_user_id, $message);

    // tell the world
    $ret->message = 'Your message was sent successfully!';
} catch (Exception $e) {
    $ret->success = false;
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
