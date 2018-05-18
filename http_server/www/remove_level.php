<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/levels/level_select.php'; // select a level
require_once QUERIES_DIR . '/staff/level_unpublish.php'; // unpublish a level
require_once QUERIES_DIR . '/staff/actions/mod_action_insert.php'; // record the mod action

$level_id = (int) default_post('level_id', 0);

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
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
    if ($mod->can_unpublish_level != 1) {
        throw new Exception('You can not unpublish levels.');
    }

    // check for the level's information
    $level = level_select($pdo, $level_id);
    $l_title = $level->title;
    $l_creator = id_to_name($pdo, $level->user_id);
    $l_note = $level->note;

    // unpublish the level
    level_unpublish($pdo, $level_id);

    //action log
    $name = $mod->name;
    $user_id = $mod->user_id;
    $ip = $mod->ip;

    //record the change
    mod_action_insert(
        $pdo,
        $user_id,
        "$name unpublished level $level_id from $ip {level_title: $l_title, creator: $l_creator, level_note: $l_note}",
        0,
        $ip
    );

    //tell it to the world
    die('message=This level has been removed successfully. It may take up to 60 '.
        'seconds for this change to take effect.');
} catch (Exception $e) {
    $error = $e->getMessage();
    die("error=$error");
}
