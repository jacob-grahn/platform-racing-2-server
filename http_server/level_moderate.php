<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/best_levels.php';
require_once QUERIES_DIR . '/campaigns.php';
require_once QUERIES_DIR . '/level_prizes.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/new_levels.php';

$level_id = (int) default_post('level_id', 0);
$action = default_post('action', 'unpublish');
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

    // sanity check: valid mode?
    if ($action !== 'unpublish' && $action !== 'restrict') {
        throw new Exception('Invalid action specified.');
    }

    // rate limiting
    rate_limit('moderate-level-'.$ip, 3, 1);

    // connect
    $pdo = pdo_connect();

    // make sure the user is a moderator
    $mod = check_moderator($pdo);

    // more rate limiting
    rate_limit('moderate-level-'.$mod->user_id, 3, 1);

    // moderate level
    moderate_level($pdo, $mod, $level_id, $action);

    // tell it to the world
    $ret->success = true;
    $suppl = $action === 'restrict' ? ' from all level lists' : '';
    $ret->message = "This level has been ${action}ed successfully. It may take up to 60 seconds to disappear$suppl.";
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
