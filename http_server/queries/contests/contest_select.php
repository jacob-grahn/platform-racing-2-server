<?php

function contest_select($pdo, $contest_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM contests
        WHERE contest_id = :contest_id
        AND active = 1
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select contest.');
    }
    
    $contest = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($contest)) {
        if ($suppress_error === false) {
            throw new Exception("Could not find an active contest with that ID.");
        } else {
            return false;
        }
    }
    
    return $contest;
}
