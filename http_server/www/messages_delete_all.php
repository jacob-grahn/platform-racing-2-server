<?php

header("Content-type: text/plain");
require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/messages/messages_delete_all.php';

$ip = get_ip();

try {
    // check referrer
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only delete all of your PMs from an approved site such as pr2hub.com.");
    }

    // rate limiting
    rate_limit('delete-all-messages-'.$ip, 900, 1, 'You may only delete all of your PMs once every 15 minutes. Try again later.');

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('delete-all-messages-'.$user_id, 900, 1, 'You may only delete all of your PMs once every 15 minutes. Try again later.');

    // delete their PMs
    messages_delete_all($pdo, $user_id);

    // tell the world
    echo 'message=All of your PMs have been deleted!';

    // seeya fam
    die();
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
    die();
}
