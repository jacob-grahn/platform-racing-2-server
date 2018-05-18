<?php

function level_check_if_creator($pdo, $user_id, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT level_id
          FROM pr2_levels
         WHERE user_id = :user_id
           AND level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query level_check_if_creator.');
    }
    
    $level = (bool) $stmt->fetch(PDO::FETCH_OBJ);
    return $level;
}
