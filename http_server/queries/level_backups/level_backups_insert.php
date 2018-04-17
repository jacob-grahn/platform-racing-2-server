<?php

function level_backups_insert(
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
) {
    $stmt = $pdo->prepare('
        INSERT INTO level_backups
        SET user_id = :user_id,
            level_id = :level_id,
            title = :title,
            version = :version,
            live = :live,
            rating = :rating,
            votes = :votes,
            note = :note,
            min_level = :min_level,
            song = :song,
            play_count = :play_count,
            date = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':version', $version, PDO::PARAM_INT);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_STR);
    $stmt->bindValue(':votes', $votes, PDO::PARAM_INT);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $min_level, PDO::PARAM_INT);
    $stmt->bindValue(':song', $song, PDO::PARAM_INT);
    $stmt->bindValue(':play_count', $play_count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not insert level backup.');
    }

    return $result;
}
