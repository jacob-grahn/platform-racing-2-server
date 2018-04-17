<?php

function user_select_guest($pdo)
{
    $stmt = $pdo->prepare('
        SELECT user_id, name
          FROM users
         WHERE power = 0
           AND status = "offline"
         LIMIT 1
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query user_select_guest.');
    }

    $guest = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($guest)) {
        throw new Exception('Could not find a suitable guest account. '
            .'Try again later, or create a new account instead.');
    }

    return $guest;
}
