<?php

function level_update_rating($pdo, $level_id, $rating, $votes)
{
    $stmt = $pdo->prepare('
        UPDATE pr2_levels
           SET rating = :rating,
               votes = :votes
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_STR);
    $stmt->bindValue(':votes', $votes, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not update level rating.');
    }

    return $result;
}
