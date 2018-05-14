<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/messages/messages_delete_all.php';

$ip = get_ip();

try {
    // check referrer
    require_trusted_ref('delete all of your PMs');

    // rate limiting
    rate_limit(
        'delete-all-messages-'.$ip,
        900,
        1,
        'You may only delete all of your PMs once every 15 minutes. Try again later.'
    );

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
    rate_limit(
        'delete-all-messages-'.$user_id,
        900,
        1,
        'You may only delete all of your PMs once every 15 minutes. Try again later.'
    );

    // delete their PMs
    messages_delete_all($pdo, $user_id);

    // tell the world
    echo 'message=All of your PMs have been deleted!';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
