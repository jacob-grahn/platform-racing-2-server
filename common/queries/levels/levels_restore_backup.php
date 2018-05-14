<?php

function levels_restore_backup(
    $pdo,
    $user_id,
    $title,
    $note,
    $live,
    $time,
    $ip,
    $min_level,
    $song,
    $level_id,
    $play_count,
    $votes,
    $rating,
    $version
) {
    $stmt = $pdo->prepare('
        INSERT INTO pr2_levels
        SET level_id = :level_id,
            version = :version,
            title = :title,
            note = :note,
            live = :live,
            time = :time,
            ip = :ip,
            min_level = :min_level,
            song = :song,
            user_id = :user_id,
            play_count = :play_count,
            votes = :votes,
            rating = :rating
        ON DUPLICATE KEY UPDATE
            version = version + 1,
            note = :note,
            live = :live,
            min_level = :min_level,
            song = :song
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $min_level, PDO::PARAM_INT);
    $stmt->bindValue(':song', $song, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':play_count', $play_count, PDO::PARAM_INT);
    $stmt->bindValue(':votes', $votes, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_STR);
    $stmt->bindValue(':version', $version, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not restore level backup.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
