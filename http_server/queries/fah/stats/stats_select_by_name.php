<?php

function stats_select_by_name($pdo, $fah_name)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM stats
         WHERE fah_name = :fah_name
         LIMIT 1
    ');
    $stmt->bindValue(':fah_name', $fah_name, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform fah db query stats_select_by_name.');
    }
    
    return $stmt->fetch(PDO::FETCH_OBJ);
}
