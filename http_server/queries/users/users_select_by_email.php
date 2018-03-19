<?php

function users_select_by_email($pdo, $email)
{
    $stmt = $pdo->prepare('
        SELECT power, name, active_date
        FROM users
        WHERE email = :email
        ORDER BY active_date DESC
    ');
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not search for a user with that email.');
    }

    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if ($user === false) {
        throw new Exception('Could not find a user with that email.');
    }

    return $user;
}
