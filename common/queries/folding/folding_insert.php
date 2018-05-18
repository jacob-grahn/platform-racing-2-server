<?php

function folding_insert($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO folding_at_home
        SET user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not create a folding_at_home row for user #$user_id.");
    }

    return $result;
}
