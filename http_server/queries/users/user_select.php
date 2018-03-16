<?php

function user_select($pdo, $user_id)
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
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if ($result === false) {
        throw new Exception('Could not find a user with that ID.');
    }

    return $result;
}
