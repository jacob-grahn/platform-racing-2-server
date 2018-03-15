<?php

header("Content-type: text/plain");
require_once __DIR__ . '/../fns/all_fns.php';

$target_name = $_POST['target_name'];
$safe_name = htmlspecialchars($target_name);
$ip = get_ip();

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('ignored-list-'.$ip, 3, 2);

    // connect
    $db = new DB();
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('ignored-list-'.$user_id, 3, 2);

    // get the id of the un-ignored player
    $target_id = name_to_id($db, $target_name);

    // reconcile the differences :)
    $db->call('ignored_delete', array($user_id, $target_id));

    // tell the world
    echo "message=$safe_name has been un-ignored. You will now recieve any chat or private messages they send you.";
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
