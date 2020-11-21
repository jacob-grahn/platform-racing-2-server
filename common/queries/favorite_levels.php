<?php


function favorite_level_delete($pdo, $user_id, $level_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM favorite_levels
         WHERE user_id = :user_id
           AND level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not remove this level from your favorites.');
    }

    return $result;
}


function favorite_level_insert($pdo, $user_id, $level_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO favorite_levels
           SET user_id = :user_id,
               level_id = :level_id,
               time_added = UNIX_TIMESTAMP(NOW())
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not add this level to your favorites.');
    }

    return $result;
}


function favorite_levels_select_ids($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT level_id
          FROM favorite_levels
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Could not perform query favorite_levels_select_ids.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function favorite_levels_select($pdo, $user_id, $page)
{
    db_set_encoding($pdo, 'utf8mb4');
    $start = ($page - 1) * 9;
    $stmt = $pdo->prepare('
        SELECT l.level_id,
            l.version,
            l.title,
            l.rating,
            l.play_count,
            l.min_level,
            l.note,
            l.live,
            l.pass,
            l.type,
            l.bad_hats,
            l.time,
            u.name,
            u.power,
            u.trial_mod
        FROM favorite_levels fl
        LEFT JOIN levels l ON fl.level_id = l.level_id
        LEFT JOIN users u ON l.user_id = u.user_id
        WHERE fl.user_id = :user_id
        AND (l.live = 1 OR (l.live = 0 AND l.pass IS NOT NULL))
        ORDER BY fl.time_added DESC
        LIMIT :start, 9
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select your favorite levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
