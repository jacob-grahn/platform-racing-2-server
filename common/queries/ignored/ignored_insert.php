<?php

function ignored_insert($pdo, $user_id, $ignore_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO ignored
               SET user_id = :user_id,
                   ignore_id = :ignore_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ignore_id', $ignore_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not add this user to your ignored players list.');
    }

    return $result;
}
