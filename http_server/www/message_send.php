<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/pr2_fns.php';

header("Content-type: text/plain");

$to_name = $_POST['to_name'];
$message = $_POST['message'];
$ip = get_ip();

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

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
    echo 'message=Your message was sent successfully!';
} catch (Exception $e) {
    $message = $e->getMessage();
    echo 'error=' . $message;
}
