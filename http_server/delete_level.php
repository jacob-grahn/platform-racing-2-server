<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/artifact_location.php';
require_once QUERIES_DIR . '/campaigns.php';
require_once QUERIES_DIR . '/level_backups.php';
require_once QUERIES_DIR . '/level_prizes.php';
require_once QUERIES_DIR . '/new_levels.php';

$level_id = (int) default_post('level_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // check referrer
    require_trusted_ref('delete levels');

    // sanity check
    if (is_empty($level_id, false)) {
        throw new Exception('No level ID was specified.');
    }

    // rate limiting
    $rl_msg = 'Please wait at least 10 seconds before trying to delete another level.';
    rate_limit('delete-level-attempt-'.$ip, 10, 1, $rl_msg);

    //connect
    $pdo = pdo_connect();
    $s3 = s3_connect();

    // check their login
    $user_id = (int) token_login($pdo, false, false, 'g');
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception('Guests can\'t delete levels. To access this feature, please create your own account.');
    }

    // more rate limiting
    rate_limit('delete-level-attempt-'.$user_id, 10, 1, $rl_msg);

    // fetch level data
    $level = level_select($pdo, $level_id);
    if ((int) $level->user_id !== $user_id) {
        throw new Exception('This is not your level.');
    }

    // check if this is currently (or will be) the level of the week
    if (is_arti_level(artifact_locations_select($pdo), $level_id)) {
        $msg = 'Your level could not be deleted because it is or will be the Level of the Week. '
            .'Please contact a member of the PR2 Staff Team for more information.';
        throw new Exception($msg);
    }

    // check to see if this level has a prize
    if (!empty(campaign_level_select_by_id($pdo, $level_id)) || !empty(level_prize_select($pdo, $level_id))) {
        throw new Exception('Your level could not be deleted because it is has a prize.');
    }

    // even more rate limiting
    rate_limit('delete-level-'.$ip, 3600, 5, 'You may only delete 5 levels per hour. Try again later.');
    rate_limit('delete-level-'.$user_id, 3600, 5, 'You may only delete 5 levels per hour. Try again later.');

    // save this file to the backup system
    backup_level(
        $pdo,
        $s3,
        $user_id,
        $level_id,
        $level->version,
        $level->title,
        $level->live,
        $level->rating,
        $level->votes,
        $level->note,
        $level->min_level,
        $level->song,
        $level->play_count,
        $level->pass,
        $level->type,
        $level->bad_hats
    );

    // delete the level in the db
    level_delete($pdo, $level_id);
    delete_from_newest($pdo, $level_id);

    // delete the file from server
    unlink(__DIR__ . "/levels/$level_id.txt");

    // delete the file from s3
    if (!$s3->deleteObject('pr2levels1', "$level_id.txt")) {
        throw new Exception('A server error was encountered. Your level could not be deleted.');
    }

    // tell the world
    $ret->success = true;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
