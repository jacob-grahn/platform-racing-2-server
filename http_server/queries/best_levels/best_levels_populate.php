<?php

function best_levels_populate($pdo)
{
    $result = $pdo->exec('
        INSERT INTO best_levels
            SELECT level_id
            FROM pr2_levels
            WHERE live = 1
            AND votes > 1000
            AND rating > 4.3
            LIMIT 100
    ');

    if ($result === false) {
        throw new Exception('Could not populate all-time best levels.');
    }

    return $result;
}
