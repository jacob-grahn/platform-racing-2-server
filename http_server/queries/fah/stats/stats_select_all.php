<?php

// TO-DO: is this needed?
function stats_select_all($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM stats');
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select all folding at home stats.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
