<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/level_backups/level_backup_select.php';
require_once QUERIES_DIR . '/level_backups/level_backups_select.php';
require_once QUERIES_DIR . '/levels/level_select.php';
require_once QUERIES_DIR . '/levels/levels_restore_backup.php';

$ip = get_ip();
$desc = "<p><center>".
        "Welcome to PR2's level restore system!<br>".
        "You can use this tool to restore any level that was modified or deleted in the past month.".
        "</center></p>";

try {
    // rate limiting
    rate_limit('level-backups-'.$ip, 5, 2);
    rate_limit('level-backups-'.$ip, 30, 10);

    // connect
    $pdo = pdo_connect();
    $user_id = token_login($pdo);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Level Backups");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // rate limiting
    rate_limit('level-backups-'.$user_id, 5, 1);
    rate_limit('level-backups-'.$user_id, 30, 5);

    // output mod nav if they're a mod
    $is_mod = is_moderator($pdo, false);
    output_header('Level Backups', $is_mod);

    // restore a backup
    $action = find('action');

    if ($action == 'restore') {
        // check referrer
        require_trusted_ref('restore level backups');

        // get the level_id that this backup_id points to
        $backup_id = find('backup_id');
        $row = level_backup_select($pdo, $backup_id);
        if ($row->user_id != $user_id) {
            throw new Exception('You do not own this backup.');
        }

        // initialize some variables
        $level_id = $row->level_id;
        $version = $row->version;
        $title = $row->title;
        $ip = get_ip();
        $time = time();

        // connect
        $s3 = s3_connect();

        // pull the backup
        $file = $s3->getObject('pr2backups', "$level_id-v$version.txt");
        if (!$file) {
            throw new Exception('Could not load backup contents.');
        }
        $body = $file->body;

        // restore this backup to the db
        levels_restore_backup(
            $pdo,
            $user_id,
            $title,
            $row->note,
            $row->live,
            $time,
            $ip,
            $row->min_level,
            $row->song,
            $level_id,
            $row->play_count,
            $row->votes,
            $row->rating,
            $version
        );
        $restored_level = level_select($pdo, $level_id);
        $new_version = $restored_level->version;

        // increment the version and recalculate the hash of the level body
        $str1 = "&version=$version";
        $str2 = "&version=$new_version";
        $body = str_replace($str1, $str2, $body);
        $len = strlen($body) - 32;
        $body = substr($body, 0, $len);
        $str_to_hash = $new_version . $level_id . $body . $LEVEL_SALT_2;
        $hash = md5($str_to_hash);
        $body = $body . $hash;

        // write the backup to the level system
        $result = $s3->putObjectString($body, 'pr2levels1', "$level_id.txt");
        if (!$result) {
            throw new Exception('Could not restore backup.');
        }

        // success
        echo $desc;
        echo '<p>---</p>';
        echo "<p><b>".htmlspecialchars($title)." v$version</b> restored successfully!</p>";
    } else {
        echo $desc;
    }


    // display available backups
    echo '<br/>';
    $backups = level_backups_select($pdo, $user_id);
    foreach ($backups as $row) {
        echo "<p>$row->date: <b>".htmlspecialchars($row->title)
            ."</b> v$row->version <a href='?action=restore&backup_id=$row->backup_id'>restore</a></p>";
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
    die();
}
