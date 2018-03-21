<?php

header("Content-type: text/plain");
require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/ignored/ignored_insert.php';

$ignored_name = $_POST['target_name'];
$safe_ignored_name = htmlspecialchars($ignored_name);
$ip = get_ip();

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('ignored-list-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('ignored-list-'.$user_id, 3, 2);

    // get the ignored user's id
    $ignored_id = name_to_id($pdo, $ignored_name);

    // create the restraining order
    ignored_insert($pdo, $user_id, $ignored_id);

    // tell it to the world
    echo "message=$safe_ignored_name has been ignored. You won't recieve any chat or private messages from them.";
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
    exit;
}
