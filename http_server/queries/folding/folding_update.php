<?php

function folding_update($pdo, $user_id, $column_name)
{
    $stmt = $pdo->prepare('
        UPDATE folding_at_home
        SET :column_name = 1
        WHERE user_id = :user_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':column_name', $column_name, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update folding_at_home row');
    }

    return $result;
}
