<?php

function token_insert($pdo, $user_id, $token)
{
    $stmt = $pdo->prepare('
        INSERT INTO tokens
           SET user_id = :user_id,
               token = :token,
    		   time = NOW()
        ON DUPLICATE KEY UPDATE
    		   token = :token,
    		   time = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not create a login token for you.');
    }

    return $result;
}
