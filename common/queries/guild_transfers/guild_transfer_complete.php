<?php

function guild_transfer_complete($pdo, $transfer_id, $ip)
{
    $stmt = $pdo->prepare('
        UPDATE guild_transfers
           SET status = "complete",
               confirm_ip = :ip
         WHERE transfer_id = :transfer_id
    ');
    $stmt->bindValue(':transfer_id', $transfer_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not complete the guild transfer.');
    }

    return $result;
}
