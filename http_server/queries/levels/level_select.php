<?php

function level_select($pdo, $level_id)
{
	$stmt = $pdo->prepare('
        SELECT *
        FROM pr2_levels
        WHERE level_id = :level_id
        LIMIT 1
    ');
	$stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
	$stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        throw new Exception('level not found');
    }

    return $result;
}
