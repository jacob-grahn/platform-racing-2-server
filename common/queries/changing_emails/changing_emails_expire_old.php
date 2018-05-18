<?php

function changing_emails_expire_old($pdo)
{
    $result = $pdo->exec('
        UPDATE changing_emails
           SET status = "expired",
               confirm_ip = "n/a"
         WHERE DATE(date) < DATE_SUB(NOW(), INTERVAL 1 DAY)
           AND status = "pending"
    ');
    
    if ($result === false) {
        throw new Exception('Could not expire old email changes.');
    }
    
    return $result;
}
