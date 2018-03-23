<?php

function name_to_id($pdo, $name, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT user_id
        FROM users
        WHERE name = :name
        LIMIT 1
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception("Could not find a user with that name.");
        } else {
            return false;
        }
    }

    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($user === false) {
        if ($suppress_error === false) {
            throw new Exception('name_to_id: User not found.');
        } else {
            return false;
        }
    }

    return $user->user_id;
}
