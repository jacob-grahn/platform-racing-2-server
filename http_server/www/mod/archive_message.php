<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/staff/actions/mod_action_insert.php';
require_once QUERIES_DIR . '/messages_reported/messages_reported_archive.php';

$message_id = (int) default_get('message_id', 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-archive-message-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // archive the message
    messages_reported_archive($pdo, $message_id);

    // record the change
    $mod_id = $mod->user_id;
    $mod_name = $mod->name;
    mod_action_insert($pdo, $mod_id, "$mod_name archived the report of PM $message_id from $ip.", 0, $ip);

    // tell the sorry saps trying to debug
    $ret = new stdClass();
    $ret->success = true;
    $ret->message_id = $message_id;
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
    $ret->message_id = $message_id;
} finally {
    echo json_encode($ret);
    die();
}
