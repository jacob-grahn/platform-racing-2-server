<?php

function part_awards_select_list($pdo)
{
    $stmt = $pdo->prepare('
        SELECT user_id, type, part
        FROM part_awards
    ');
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not fetch the list of part awards.');
    }
    
    $awards = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($awards)) {
        return false;
    }

    return $awards;
}
