<?php

function stats_select_by_name($pdo, $fah_name, $suppress_error = false)
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
    
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($row) && $suppress_error === true) {
        return false;
    }
    
    return $row;
}
