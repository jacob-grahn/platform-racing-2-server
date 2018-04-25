<?php

function user_select($pdo, $user_id, $suppress_error = false)
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
         WHERE user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query user_select.');
    }
    
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($user) && $suppress_error === false) {
        throw new Exception('Could not find a user with that ID.');
    }

    return $user;
}
