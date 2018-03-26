<?php

function best_levels_truncate($pdo)
{
    $result = $pdo->exec('TRUNCATE TABLE best_levels');
    
    if ($result === false) {
        throw new Exception("Could not truncate all-time best levels table.");
    }
    
    return $result;
}
