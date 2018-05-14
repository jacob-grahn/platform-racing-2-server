<?php

function user_apply_temp_pass($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET pass_hash = temp_pass_hash,
               temp_pass_hash = NULL
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not apply temporary password.');
    }

    return $result;
}
