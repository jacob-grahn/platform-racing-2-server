<?php

function rating_select($pdo, $level_id, $user_id, $ip)
{
    $stmt = $pdo->prepare('
        SELECT rating, weight
          FROM pr2_ratings
         WHERE level_id = :level_id
           AND (user_id = :user_id OR ip = :ip)
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query rating_select.');
    }

    $rating = $stmt->fetch(PDO::FETCH_OBJ);
    return $rating;
}
