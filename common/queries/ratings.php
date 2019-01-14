<?php


function rating_insert($pdo, $level_id, $rating, $user_id, $weight, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO ratings
           SET level_id = :level_id,
               rating = :rating,
               user_id = :user_id,
               weight = :weight,
               time = UNIX_TIMESTAMP(NOW()),
               ip = :ip
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':weight', $weight, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record your rating.');
    }

    return $result;
}


function rating_select($pdo, $level_id, $user_id, $ip)
{
    $stmt = $pdo->prepare('
        SELECT rating, weight
          FROM ratings
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


function ratings_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM ratings
         WHERE time < UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)
    ');

    if ($result === false) {
        throw new Exception('Could not delete rating logs older than a week.');
    }

    return $result;
}
