<?php

function best_levels_populate($pdo)
{
	$stmt = $pdo->prepare('
        TRUNCATE TABLE best_levels;

        INSERT INTO best_levels
        SELECT level_id
        FROM pr2_levels
        WHERE live = 1
        AND votes > 1000
        AND rating > 4.3;
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('could not populate best levels');
    }

    return $result;
}
