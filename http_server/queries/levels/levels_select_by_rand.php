<?php

function levels_select_by_rand($pdo)
{
    $stmt = $pdo->prepare('
        SELECT level_id, title, note
          FROM pr2_levels
          JOIN (SELECT CEIL(RAND() * (SELECT MAX(level_id) FROM pr2_levels)) AS random_level_id) AS temp
         WHERE pr2_levels.level_id >= temp.random_level_id
           AND live = 1
         ORDER BY level_id ASC
         LIMIT 1
    ');
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query to select a random level.');
    }
    
    $level = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($level)) {
        throw new Exception('Could not find a random level.');
    }

    return $level;
}
