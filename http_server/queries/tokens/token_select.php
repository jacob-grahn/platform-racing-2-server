<?php

function token_select($pdo, $token_id)
{
    $stmt = $pdo->prepare('
        SELECT user_id, token
          FROM tokens
         WHERE token = :token_id
         LIMIT 1
    ');
    $stmt->bindValue(':token_id', $token_id, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query token_select.');
    }
    
    $token = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($token)) {
        throw new Exception('Could not find a valid login token. Please log in again.');
    }

    return $token;
}
