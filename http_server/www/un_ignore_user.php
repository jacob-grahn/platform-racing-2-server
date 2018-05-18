<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/ignored/ignored_delete.php';

$target_name = $_POST['target_name'];
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
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception(
            "Guests can't add/remove users to/from their friends/ignored lists. ".
            "To access this feature, please create your own account."
        );
    }

    // more rate limiting
    rate_limit('ignored-list-'.$user_id, 3, 2);

    // get the id of the un-ignored player
    $target_id = name_to_id($pdo, $target_name);

    // reconcile the differences :)
    ignored_delete($pdo, $user_id, $target_id);

    // tell the world
    $safe_name = htmlspecialchars($target_name);
    echo "message=$safe_name has been un-ignored. You will now recieve any chat or private messages they send you.";
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
