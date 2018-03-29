<?php

function level_delete($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM pr2_levels
        WHERE level_id = :level_id
        LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete level.');
    }

    return $result;
}
