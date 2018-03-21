<?php

function rank_token_select($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM rank_tokens
        WHERE user_id = :user_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('could not select rank token');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
