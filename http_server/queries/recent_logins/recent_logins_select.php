<?php

function recent_logins_select($pdo, $user_id, $suppress_error = false, $count = 100)
{
    $count = (int) $count;
    $stmt = $pdo->prepare('
        SELECT * FROM recent_logins
        WHERE user_id = :user_id
        ORDER BY date DESC
        LIMIT 0 , :count
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if($result === false) {
        if ($suppress_error === false) {
            throw new Exception("Could not find any recent logins for this user.");
        } else {
            return false;
        }
    }
    
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
