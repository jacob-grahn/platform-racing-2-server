<?php


function best_levels_reset($pdo)
{
    best_levels_truncate($pdo);
    best_levels_populate($pdo);
}


// consider calling with best_levels_reset!
function best_levels_truncate($pdo)
{
    $result = $pdo->exec('TRUNCATE TABLE best_levels');

    if ($result === false) {
        throw new Exception("Could not truncate all-time best levels table.");
    }

    return $result;
}


// consider calling with best_levels_reset!
function best_levels_populate($pdo)
{
    $result = $pdo->exec('
        INSERT INTO best_levels
             SELECT level_id
               FROM levels
              WHERE live = 1
                AND votes > 1000
                AND rating > 4.3
           ORDER BY rating DESC
              LIMIT 100
    ');

    if ($result === false) {
        throw new Exception('Could not populate all-time best levels.');
    }

    return $result;
}
