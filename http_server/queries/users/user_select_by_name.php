<?php

function user_select_by_name($pdo, $name, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT user_id,
               name,
               email,
               register_ip,
               ip,
               time,
               register_time,
               power,
               status,
               read_message_id,
               guild,
               server_id
          FROM users
          WHERE name = :name
          LIMIT 1
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query user_select_by_name.');
    }

    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($user) && $suppress_error === false) {
        throw new Exception('Could not find a user with that name.');
    }

    return $user;
}
