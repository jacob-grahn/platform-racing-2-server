<?php

function id_to_name($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT name
          FROM users
         WHERE user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query id_to_name.');
    }
    
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($user)) {
        if ($suppress_error === false) {
            throw new Exception('id_to_name: Could not find a user with that ID.');
        } else {
            return false;
        }
    }
    
    return $user->name;
}
