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
    if (!$result) {
        throw new Exception('Could not fetch user by name.');
    }

    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if ($user === false && $suppress_error === false) {
        throw new Exception('Could not find a user with that name.');
    }

    return $user;
}
