<?php

function recent_logins_select($pdo, $user_id, $suppress_error = false, $start = 0, $count = 100)
{
    $start = (int) $start;
    $count = (int) $count;
    
    $stmt = $pdo->prepare('
        SELECT *
          FROM recent_logins
         WHERE user_id = :user_id
         ORDER BY date DESC
         LIMIT :start , :count
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception("Could not perform query recent_logins_select.");
        } else {
            return false;
        }
    }
    
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
