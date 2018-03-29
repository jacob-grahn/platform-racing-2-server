<?php

function user_update_status($pdo, $user_id, $status, $server_id)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET time = UNIX_TIMESTAMP(NOW()),
               active_date = NOW(),
               status = :status,
               server_id = :server_id
         WHERE user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':server_id', $server_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update the status of user #$user_id.");
    }

    return $result;
}
