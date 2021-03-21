<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/campaigns.php';
require_once QUERIES_DIR . '/level_prizes.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/new_levels.php';

$level_id = (int) default_post('level_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // sanity check: was a level ID specified?
    if (is_empty($level_id, false)) {
        throw new Exception('No level ID was specified.');
    }

    // rate limiting
    rate_limit('remove-level-'.$ip, 3, 1);

    // connect
    $pdo = pdo_connect();

    // make sure the user is a moderator
    $mod = check_moderator($pdo);

    // more rate limiting
    rate_limit('remove-level-'.$mod->user_id, 3, 1);

    // unpublish level
    remove_level($pdo, $mod, $level_id);

    // tell it to the world
    $ret->success = true;
    $ret->message = 'This level has been removed successfully. It may take up to 60 seconds to disappear.';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
