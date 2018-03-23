<?php

function user_select_hash_by_name($pdo, $name)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM users
         WHERE name = :name
         LIMIT 1
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if ($result === false) {
        throw new Exception('That username / password combination was not found.');
    }

    return $result;
}
