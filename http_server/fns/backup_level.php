<?php

function backup_level(
    $pdo,
    $s3,
    $user_id,
    $level_id,
    $version,
    $title,
    $live = 0,
    $rating = 0,
    $votes = 0,
    $note = '',
    $min_level = 0,
    $song = 0,
    $play_count = 0
) {
    $filename = "$level_id.txt";
    $backup_filename = "$level_id-v$version.txt";
    $success = true;

    try {
        $result = $s3->copyObject('pr2levels1', $filename, 'pr2backups', $backup_filename);
        if (!$result) {
            throw new Exception('Could not save a backup of your level.');
        }

        level_backups_insert(
            $pdo,
            $user_id,
            $level_id,
            $title,
            $version,
            $live,
            $rating,
            $votes,
            $note,
            $min_level,
            $song,
            $play_count
        );
    } catch (Exception $e) {
        $success = false;
    }

    return $success;
}
