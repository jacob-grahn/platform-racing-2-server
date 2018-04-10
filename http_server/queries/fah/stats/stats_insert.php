<?php

function stats_insert($pdo, $name, $wu, $points, $rank)
{
    $stmt = $pdo->prepare('
        INSERT INTO stats
        SET fah_name = :name,
                wu = :wu,
                points = :points,
                rank = :rank
        ON DUPLICATE KEY UPDATE
                wu = :wu,
                points = :points,
                rank = :rank
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':wu', $wu, PDO::PARAM_INT);
    $stmt->bindValue(':points', $points, PDO::PARAM_INT);
    $stmt->bindValue(':rank', $rank, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update fah stats');
    }

    return $result;
}
