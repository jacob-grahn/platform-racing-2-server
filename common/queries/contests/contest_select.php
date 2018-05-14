<?php

function contest_select($pdo, $contest_id, $active_only = true, $suppress_error = false)
{
    if ($active_only === true) {
        $active_cond = 'AND active = 1';
    } else {
        $active_cond = '';
    }
    
    $stmt = $pdo->prepare("
        SELECT contest_id, contest_name, description, url, user_id, awarding, max_awards, active
        FROM contests
        WHERE contest_id = :contest_id
        $active_cond
        LIMIT 1
    ");
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select contest.');
    }
    
    $contest = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($contest)) {
        if ($suppress_error === false) {
            if ($active_only === true) {
                throw new Exception("Could not find an active contest with that ID.");
            } else {
                throw new Exception("Could not find a contest with that ID.");
            }
        } else {
            return false;
        }
    }
    
    return $contest;
}
