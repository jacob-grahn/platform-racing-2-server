<?php


function level_prizes_select($pdo)
{
    $stmt = $pdo->query('
        SELECT level_id, type, id
        FROM level_prizes
    ');

    if ($stmt === false) {
        throw new Exception('Could not fetch level prizes.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function level_prize_select($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT
          type,
          id
        FROM
          level_prizes
        WHERE
          level_id = :level_id
        LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select level prize.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
