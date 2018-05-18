<?php

function contests_select($pdo, $active_only = true)
{
    $sql = '';
    $err = 'all ';
    if ($active_only === true) {
        $sql = 'WHERE active = 1';
        $err = 'active ';
    }
    
    $stmt = $pdo->prepare("
        SELECT contest_id, contest_name, description, url, user_id, awarding, active, max_awards
          FROM contests
          $sql
         ORDER BY active DESC, contest_name ASC
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
