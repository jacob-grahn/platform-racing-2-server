<?php

function contests_select($pdo, $active_only = true)
{
    if ($active_only === true) {
        $sql = 'WHERE active = 1';
        $err = 'active ';
    } else {
        $sql = '';
        $err = 'all ';
    }
    
    $stmt = $pdo->prepare("
        SELECT contest_id, contest_name, description, url, user_id, active
        FROM contests
        $sql
    ");
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select '.$err.'contests.');
    }
    
    $contests = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($contests)) {
        return false;
    }
    
    return $contests;
}
