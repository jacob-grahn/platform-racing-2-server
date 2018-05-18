<?php

function user_select_power_by_name($pdo, $name)
{
    $stmt = $pdo->prepare('
        SELECT power
          FROM users
         WHERE name = :name
         LIMIT 1
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not perform query user_select_power_by_name.");
    }
    
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($user)) {
        throw new Exception("Could not find a user with that name.");
    }
    
    return $user->power;
}
