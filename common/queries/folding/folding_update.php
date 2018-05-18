<?php

function folding_update($pdo, $user_id, $column_name)
{
    $columns = ['r1', 'r2', 'r3', 'r4', 'r5', 'crown_hat', 'cowboy_hat'];
    if (array_search($column_name, $columns) === false) {
        throw new Exception('Invalid column name in folding_update');
    }

    $stmt = $pdo->prepare("
        UPDATE folding_at_home
           SET $column_name = 1
         WHERE user_id = :user_id
         LIMIT 1
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update folding_at_home entry for user #$user_id at column $column_name.");
    }

    return $result;
}
