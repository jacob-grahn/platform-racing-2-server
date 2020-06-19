<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/levels_reported.php';

$level_id = (int) default_post('level_id', 0);
$version = (int) default_post('version', 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('level-reported-load-'.$ip, 5, 1);

    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // connect
    $pdo = pdo_connect();

    // check if logged in and a moderator
    $user_id = (int) token_login($pdo, true, false, 'g');
    check_moderator($pdo, $user_id);

    // more rate limiting
    rate_limit('level-reported-load-'.$user_id, 5, 2);

    // sanity: valid level id?
    if ($level_id <= 0) {
        throw new Exception('Invalid level ID.');
    }

    // sanity: valid version?
    if ($version <= 0) {
        throw new Exception('Invalid version number.');
    }

    // check for a valid level report
    $level = level_report_select_load_info($pdo, $level_id, $version);
    if (empty($level->reported_version)) {
        throw new Exception('Could not find a valid report for this level ID.');
    }

    // retrieve backup from s3 if the level doesn't exist or was recently modified
    if (empty($level->version) || $level->reported_version != $level->version) {
        $s3 = s3_connect();
        $file = $s3->getObject('pr2backups', "$level_id-v$level->reported_version.txt");
        if (!$file) {
            throw new Exception('Could not load backup file contents.');
        }
    } else {
        $file = file_get_contents("https://pr2hub.com/levels/$level_id.txt");
        if ($file === false) {
            throw new Exception('Could not load level file from pr2hub.com.');
        }
    }

    // echo the text file
    die($file);
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
    die(json_encode($ret));
}
