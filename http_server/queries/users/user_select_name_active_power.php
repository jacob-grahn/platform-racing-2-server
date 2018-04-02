<?php

function user_select_name_active_power($pdo, $user_id, $suppress_error = false)
{
    $count = (int) $count;
    $stmt = $pdo->prepare('
          SELECT name, time, power
            FROM users
           WHERE user_id = :user_id
           LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query users_select_name_active_power.');
    }
    
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($user)) {
        if ($suppress_error == false) {
            throw new Exception('Could not find any users with that ID.');
        } else {
            return false;
        }
    }
    
    return $user;
}
