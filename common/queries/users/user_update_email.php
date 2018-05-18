<?php

function user_update_email($pdo, $user_id, $old_email, $new_email)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET email = :new_email
         WHERE user_id = :user_id
           AND email = :old_email
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':new_email', $new_email, PDO::PARAM_STR);
    $stmt->bindValue(':old_email', $old_email, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update the email address of user #$user_id.");
    }

    return $result;
}
