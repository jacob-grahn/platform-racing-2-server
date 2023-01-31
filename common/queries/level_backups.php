<?php


function level_backup_select($pdo, $backup_id)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT *
        FROM level_backups
        WHERE backup_id = :backup_id
        LIMIT 1
    ');
    $stmt->bindValue(':backup_id', $backup_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query level_backup_select.');
    }

    $backup = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($backup)) {
        throw new Exception('Could not find a level backup with that ID.');
    }

    return $backup;
}


function level_backups_delete_old($pdo)
{
    $yearago = time() - 31536000;
    $result = $pdo->exec("DELETE FROM level_backups WHERE time < $yearago");

    if ($result === false) {
        throw new Exception('could not delete old level backups');
    }

    return $result;
}


function level_backups_insert(
    $pdo,
    $uid,
    $lid,
    $title,
    $ver,
    $live,
    $rating,
    $votes,
    $note,
    $rank,
    $song,
    $plays,
    $pass,
    $type,
    $hats
) {
    db_set_encoding($pdo, 'utf8mb4');
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
            pass = :pass,
            type = :type,
            bad_hats = :bad_hats,
            time = :time
    ');
    $stmt->bindValue(':user_id', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $lid, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':version', $ver, PDO::PARAM_INT);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_STR);
    $stmt->bindValue(':votes', $votes, PDO::PARAM_INT);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $rank, PDO::PARAM_INT);
    $stmt->bindValue(':song', $song, PDO::PARAM_INT);
    $stmt->bindValue(':play_count', $plays, PDO::PARAM_INT);
    $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':bad_hats', $hats, PDO::PARAM_STR);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not insert level backup.');
    }

    return $result;
}


function level_backups_select_by_user($pdo, $user_id)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT *
        FROM level_backups
        WHERE user_id = :user_id
        ORDER BY time DESC
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could fetch level backups for user');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
