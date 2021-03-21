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
