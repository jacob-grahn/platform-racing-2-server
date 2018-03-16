<?php

function token_select($pdo, $token_id)
{
    $result = $pdo->prepare('SELECT user_id, token FROM tokens WHERE token = :token_id LIMIT 0, 1');
    $stmt->bindValue(':token_id', $token_id, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if ($row === false) {
        throw new Exception('No login token found. Please log in again.');
    }

    return $row;
}
