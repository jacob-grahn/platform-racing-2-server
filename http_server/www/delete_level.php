<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pr2/pr2_fns.php';
require_once QUERIES_DIR . '/levels/level_select.php';
require_once QUERIES_DIR . '/levels/level_delete.php';

$level_id = (int) default_val($_POST['level_id'], 0);
$ip = get_ip();

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
    rate_limit(
        'delete-level-attempt-'.$ip,
        10,
        1,
        'Please wait at least 10 seconds before trying to delete another level.'
    );

    //connect
    $pdo = pdo_connect();
    $s3 = s3_connect();

    //check their login
    $user_id = token_login($pdo, false);
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception(
            "Guests can't delete levels. ".
            "To access this feature, please create your own account."
        );
    }

    // more rate limiting
    rate_limit(
        'delete-level-attempt-'.$user_id,
        10,
        1,
        'Please wait at least 10 seconds before trying to delete another level.'
    );
    rate_limit('delete-level-'.$ip, 3600, 5, 'You may only delete 5 levels per hour. Try again later.');
    rate_limit('delete-level-'.$user_id, 3600, 5, 'You may only delete 5 levels per hour. Try again later.');

    // fetch level data
    $row = level_select($pdo, $level_id);
    if ($row->user_id !== $user_id) {
        throw new Exception('This is not your level');
    }

    // save this file to the backup system
    backup_level(
        $pdo,
        $s3,
        $user_id,
        $level_id,
        $row->version,
        $row->title,
        $row->live,
        $row->rating,
        $row->votes,
        $row->note,
        $row->min_level,
        $row->song,
        $row->play_count
    );

    // delete the level in the db
    level_delete($pdo, $level_id);
    
    // delete the file from server
    unlink(__DIR__ . "/levels/$level_id.txt");

    // delete the file from s3
    $result = $s3->deleteObject('pr2levels1', $level_id.'.txt');
    if (!$result) {
        throw new Exception('A server error was encountered. Your level could not be deleted.');
    }

    // tell the world
    echo 'success=true';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
