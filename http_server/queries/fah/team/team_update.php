<?php

// TO-DO: is this needed?
function team_update($pdo, $wu, $points, $rank)
{
    $stmt = $pdo->prepare('
        UPDATE team
           SET wu = :wu,
               points = :points,
               rank = :rank
         LIMIT 1
    ');
    $stmt->bindValue(':wu', $wu, PDO::PARAM_INT);
    $stmt->bindValue(':points', $points, PDO::PARAM_INT);
    $stmt->bindValue(':rank', $rank, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update fah team');
    }

    return $result;
}
