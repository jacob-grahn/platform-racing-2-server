<?php

function artifact_location_update($pdo, $level_id, $x, $y)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
           SET level_id = :level_id,
               x = :x,
               y = :y,
               updated_time = NOW(),
               first_finder = 0
         WHERE artifact_id = 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':x', $x, PDO::PARAM_INT);
    $stmt->bindValue(':y', $y, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update artifact location.');
    }

    return $result;
}
