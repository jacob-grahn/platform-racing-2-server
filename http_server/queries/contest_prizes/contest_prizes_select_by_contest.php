<?php

function contest_prizes_select_by_contest($pdo, $contest_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT prize_id, part_type, part_id
          FROM contest_prizes
         WHERE contest_id = :contest_id
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select contest prizes.');
    }
    
    $prizes = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($prizes)) {
        if ($suppress_error === false) {
            throw new Exception("No prizes found for contest #$contest_id.");
        } else {
            return false;
        }
    }
    
    return $prizes;
}
