<?php

function changing_email_complete($pdo, $change_id, $ip)
{
    $stmt = $pdo->prepare('
        UPDATE changing_emails
           SET status = "complete",
               confirm_ip = :ip
         WHERE change_id = :change_id
    ');
    $stmt->bindValue(':change_id', $change_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not complete email change.');
    }

    return $result;
}
