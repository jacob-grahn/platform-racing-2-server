<?php

function levels_select_by_owner($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT pr2_levels.level_id,
               pr2_levels.version,
               pr2_levels.title,
               pr2_levels.rating,
               pr2_levels.play_count,
               pr2_levels.min_level,
               pr2_levels.note,
               pr2_levels.live,
               pr2_levels.type,
               users.name,
               users.power,
               users.user_id
          FROM pr2_levels, users
         WHERE pr2_levels.user_id = users.user_id
           AND pr2_levels.user_id = :level_id
         ORDER BY pr2_levels.time DESC
         LIMIT 0, 100
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query levels_select_by_owner.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
