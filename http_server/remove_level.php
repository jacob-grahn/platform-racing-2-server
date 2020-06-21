<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/campaigns.php';
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

    // make sure the user is a permanent moderator
    if ($mod->trial_mod) {
        throw new Exception('You can not unpublish levels.');
    }

    // check to see if this is a campaign level
    if (!empty(campaign_level_select_by_id($pdo, $level_id))) {
        throw new Exception('This level could not be unpublished because it is featured in a campaign.');
    }

    // check for the level's information
    $level = level_select($pdo, $level_id);
    $l_title = $level->title;
    $l_creator = id_to_name($pdo, $level->user_id);
    $l_note = $level->note;

    // unpublish the level
    delete_from_newest($pdo, $level_id);
    level_unpublish($pdo, $level_id);

    // record the change
    $mod_msg = "$mod->name unpublished level $level_id from $ip "
        ."{level_title: $l_title, creator: $l_creator, level_note: $l_note}";
    mod_action_insert($pdo, $mod->user_id, $mod_msg, 'remove-level', $ip);

    // tell it to the world
    $ret->success = true;
    $ret->message = 'This level has been removed successfully. It may take up to 60 seconds to disappear.';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
