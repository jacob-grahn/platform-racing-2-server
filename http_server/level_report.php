<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/levels.php';
require_once QUERIES_DIR . '/levels_reported.php';

$level_id = (int) default_post('level_id', 0);
$reason = default_post('reason', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // sanity: is there an ID being passed?
    if ($level_id <= 0) {
        throw new Exception('Invalid level specified.');
    }

    // sanity: is there a reason being passed?
    if (empty($reason)) {
        throw new Exception('Please specify the reason you\'re reporting this level.');
    }

    // rate limiting
    rate_limit('level-report-'.$ip, 5, 1, "Please wait at least 5 seconds before trying to report another level.");
    rate_limit('level-report-'.$ip, 60, 5);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, false);
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = "Guests can't use the level reporting system. To access this feature, please create your own account.";
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('level-report-'.$user_id, 5, 2, "Please wait at least 5 seconds before trying to report another level.");
    rate_limit('level-report-'.$user_id, 60, 5);

    // sanity: does the level exist?
    $level = level_select($pdo, $level_id, true);
    if ($level === false) {
        throw new Exception("The level you tried to report (#$level_id) doesn't exist.");
    }

    // sanity: is this user reporting a level from themselves?
    if ($level->user_id == $user_id) {
        throw new Exception('You can\'t report your own level, silly!');
    }

    // sanity: has this level version already been reported?
    if (levels_reported_check_existing($pdo, $level_id, $level->version) === true) {
        throw new Exception('This level version has already been reported.');
    }

    // record the report
    $version = (int) $level->version;
    $cid = (int) $level->user_id;
    $cip = $level->ip;
    $title = $level->title;
    $note = $level->note;
    $rid = $user_id;
    $rip = $ip;
    levels_reported_insert($pdo, $level_id, $version, $cid, $cip, $title, $note, $rid, $rip, $reason);

    // back up the current version of the level if one doesn't already exist
    $s3 = s3_connect();
    $file = $s3->getObject('pr2backups', "$level_id-v$level->version.txt");
    if (!$file) {
        backup_level(
            $pdo,
            $s3,
            $cid,
            $level_id,
            $version,
            $title,
            (int) $level->live,
            (float) $level->rating,
            (int) $level->votes,
            $note,
            (int) $level->min_level,
            $level->song,
            (int) $level->play_count
        );
    }

    // tell it to the world
    $ret->success = true;
    $ret->message = 'The level was reported successfully!';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
