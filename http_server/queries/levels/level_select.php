<?php

function level_select($pdo, $level_id)
{
    $stmt = $pdo->prepare('SELECT * FROM pr2_levels WHERE level_id = :level_id LIMIT 1');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if ($result === false) {
        throw new Exception('Could not find a level with that ID.');
    }

    return $result;
}
