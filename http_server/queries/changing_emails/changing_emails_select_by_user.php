<?php

function changing_email_select_by_user($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM changing_emails
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could user email change history');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
