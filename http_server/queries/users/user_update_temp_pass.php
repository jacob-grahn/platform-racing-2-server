<?php

function user_update_temp_pass($pdo, $user_id, $temp_pass_hash)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET temp_pass_hash = :temp_pass_hash
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':temp_pass_hash', $temp_pass_hash, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not set a temporary password for user #$user_id.");
    }

    return $result;
}
