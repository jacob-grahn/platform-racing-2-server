<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/messages_reported.php';

$message_id = (int) default_get('message_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('mod-archive-message-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);

    // archive the message
    messages_reported_archive($pdo, $message_id);

    // record the change
    $mod_id = $mod->user_id;
    $mod_name = $mod->name;
    mod_action_insert($pdo, $mod_id, "$mod_name archived the report of PM $message_id from $ip.", 0, $ip);

    // tell the world
    $ret->success = true;
    $ret->message_id = $message_id;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
    $ret->message_id = $message_id;
} finally {
    die(json_encode($ret));
}
