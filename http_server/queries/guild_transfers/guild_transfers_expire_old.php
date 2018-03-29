<?php

function guild_transfers_expire_old($pdo)
{
    $result = $pdo->exec('
        UPDATE guild_transfers
           SET status = "expired",
               confirm_ip = "n/a"
         WHERE DATE(date) < DATE_SUB(NOW(), INTERVAL 1 DAY)
           AND status = "pending"
    ');
    
    if ($result === false) {
        throw new Exception('Could not expire old guild transfers.');
    }
    
    return $result;
}
