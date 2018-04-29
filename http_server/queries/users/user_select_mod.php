<?php

function user_select_mod($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
            SELECT users.user_id,
                   users.name,
                   users.email,
                   users.register_ip,
                   users.ip,
                   users.time,
                   users.register_time,
                   users.power,
                   users.status,
                   users.read_message_id,
                   users.guild,
                   mod_power.*
              FROM users
        INNER JOIN mod_power
                ON users.user_id = mod_power.user_id
             WHERE users.user_id = :user_id
             LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query user_select_mod.');
    }
    
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($user) && $suppress_error === false) {
        throw new Exception('Could not find a mod with that ID.');
    }

    return $user;
}
