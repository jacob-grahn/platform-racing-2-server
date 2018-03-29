<?php

function changing_emails_select_by_user($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
          SELECT change_id, old_email, new_email, code, date, request_ip, confirm_ip, status
            FROM changing_emails
           WHERE user_id = :user_id
        ORDER BY change_id ASC
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not perform query changing_emails_select_by_user.');
        } else {
            return false;
        }
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
