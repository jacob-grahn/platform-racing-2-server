<?php

function user_update_ip($pdo, $user_id, $ip)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET time = UNIX_TIMESTAMP(NOW()),
               ip = :ip
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update the IP address for user #$user_id.");
    }

    return $result;
}
