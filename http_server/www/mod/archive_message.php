<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/staff/actions/mod_action_insert.php';
require_once __DIR__ . '/../../queries/messages_reported/messages_reported_archive.php';

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
    $name = $mod->name;
    mod_action_insert($pdo, $mod->user_id, "$name archived the report of PM $message_id from $ip.", 0, $ip);

    // tell the sorry saps trying to debug
    $ret = new stdClass();
    $ret->success = true;
    $ret->message_id = $message_id;
    echo json_encode($ret);
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
    $ret->message_id = $message_id;
    echo json_encode($ret);
}
