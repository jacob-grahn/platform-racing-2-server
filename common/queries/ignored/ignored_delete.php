<?php

function ignored_delete($pdo, $user_id, $ignore_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM ignored
         WHERE user_id = :user_id
           AND ignore_id = :ignore_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ignore_id', $ignore_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not remove this user from your ignored players list.');
    }

    return $result;
}
