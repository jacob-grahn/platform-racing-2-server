<?php

function user_select_mod($pdo, $user_id)
{
    $stmt = $pdo->prepare('
            SELECT users.user_id,
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
                         mod_power.*
              FROM users
        INNER JOIN mod_power
                ON users.user_id = mod_power.user_id
             WHERE users.user_id = :user_id
             LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ($user === false) {
        throw new Exception('Could not find a mod with that ID.');
    }

    return $user;
}
