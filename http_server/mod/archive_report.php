<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/messages_reported.php';
require_once QUERIES_DIR . '/levels_reported.php';

$message_id = (int) default_post('message_id', 0);
$level_id = (int) default_post('level_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('mod-archive-report-'.$ip, 3, 2);

    // sanity: valid input?
    if ($message_id <= 0 && $level_id <= 0) {
        throw new Exception('Invalid item.');
    }

    // sanity: only one input?
    if ($message_id > 0 && $level_id > 0) {
        throw new Exception('Please only specify one item.');
    }

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);

    // handle archive operation
    if ($message_id > 0) {
        $mode = 'message';
        $id = $message_id;
        messages_reported_archive($pdo, $message_id);
    } elseif ($level_id > 0) {
        $mode = 'level';
        $id = $level_id;
        levels_reported_archive($pdo, $level_id);
    } else {
        throw new Exception('Could not archive your item.'); // should never happen
    }

    // record the change
    $mod_id = $mod->user_id;
    $mod_name = $mod->name;
    mod_action_insert($pdo, $mod_id, "$mod_name archived the report of $mode #$id from $ip.", 'archive-report', $ip);

    // tell the world
    $ret->success = true;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    $ret->{"${mode}_id"} = $id;
    die(json_encode($ret));
}
