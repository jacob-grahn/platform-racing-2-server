<?php

function level_unpublish($pdo, $level_id)
{
    $stmt = $pdo->prepare('UPDATE pr2_levels SET live = 0, pass = NULL WHERE level_id = ?');
    $stmt->bindValue(1, $level_ID, PDO::PARAM_INT);
    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception("Could not unpublish level.");
    }
    return $result;
}
