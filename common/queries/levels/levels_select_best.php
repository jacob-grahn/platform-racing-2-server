<?php

function levels_select_best($pdo)
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
            FROM best_levels,
                 pr2_levels,
                 users
           WHERE pr2_levels.user_id = users.user_id
             AND best_levels.level_id = pr2_levels.level_id
        ORDER BY rating DESC
           LIMIT 0, 81
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select all-time best levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
