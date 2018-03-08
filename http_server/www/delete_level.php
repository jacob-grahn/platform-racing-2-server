<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/classes/S3.php';
require_once __DIR__ . '/../fns/pr2_fns.php';

$level_id = (int) default_val($_POST['level_id'], 0);
$ip = get_ip();

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // check referrer
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only delete a level from an approved site such as pr2hub.com.");
    }

    // sanity check
    if (is_empty($level_id, false)) {
        throw new Exception('No level ID was specified.');
    }

    // rate limiting
    rate_limit('delete-level-attempt-'.$ip, 10, 1, 'Please wait at least 10 seconds before trying to delete another level.');

    //connect
    $db = new DB();
    $s3 = s3_connect();

    //check their login
    $user_id = token_login($db, false);

    // more rate limiting
    rate_limit('delete-level-attempt-'.$user_id, 10, 1, 'Please wait at least 10 seconds before trying to delete another level.');
    rate_limit('delete-level-'.$ip, 3600, 5, 'You may only delete 5 levels per hour. Try again later.');
    rate_limit('delete-level-'.$user_id, 3600, 5, 'You may only delete 5 levels per hour. Try again later.');

    // save this file to the backup system
    $row = $db->grab_row('levels_select_one', array($level_id, $user_id));
    backup_level($db, $s3, $user_id, $level_id, $row->version, $row->title, $row->live, $row->rating, $row->votes, $row->note, $row->min_level, $row->song, $row->play_count);

    // delete the level in the db
    $db->call('level_delete', array($level_id, $user_id));

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
}
