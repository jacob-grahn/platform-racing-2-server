<?php

function user_select_name_and_power($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT name, power
          FROM users
         WHERE user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not perform query user_select_name_and_power.");
    }
    
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($user)) {
        throw new Exception("Could not find a user with that ID.");
    }
    
    return $user;
}
