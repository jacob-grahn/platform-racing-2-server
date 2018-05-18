<?php

function rating_insert($pdo, $level_id, $rating, $user_id, $weight, $time, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO pr2_ratings
           SET level_id = :level_id,
               rating = :rating,
               user_id = :user_id,
               weight = :weight,
               time = :time,
               ip = :ip
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':weight', $weight, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not record your rating.');
    }

    return $result;
}
