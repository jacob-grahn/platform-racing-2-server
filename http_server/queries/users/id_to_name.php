<?php

function id_to_name($pdo, $user_id)
{
    $stmt = $pdo->prepare('SELECT name FROM users WHERE user_id = :user_id LIMIT 1');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($result === false) {
        throw new Exception('Could not find a user with that ID.');
    }
    
    return $result->name;
}
