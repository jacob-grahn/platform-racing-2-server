<?php

function token_delete($pdo, $token)
{
    $stmt = $pdo->prepare('
        DELETE FROM tokens
        WHERE token = :token
    ');
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not delete your login token from the database.');
    }

    return $result;
}
