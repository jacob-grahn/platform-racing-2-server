<?php

function contests_select_active($pdo)
{
    $stmt = $pdo->prepare('
        SELECT contest_id, contest_name, description, url, user_id
        FROM contests
        WHERE active = 1
    ');
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select active contests.');
    }
    
    $contests = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($contests)) {
        return false;
    }
    
    return $contests;
}
