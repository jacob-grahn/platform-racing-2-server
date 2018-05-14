<?php

function user_update_pass($pdo, $user_id, $pass_hash)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET pass_hash = :pass_hash
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':pass_hash', $pass_hash, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not change the password of user #$user_id.");
    }

    return $result;
}
