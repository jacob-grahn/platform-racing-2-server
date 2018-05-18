<?php

function level_unpublish($pdo, $level_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        UPDATE pr2_levels
           SET live = 0,
               pass = NULL
         WHERE level_id = :level_id
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not unpublish level.');
        } else {
            return false;
        }
    }
    
    return $result;
}
