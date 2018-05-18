<?php

function rank_token_update($pdo, $user_id, $used_tokens)
{
    $stmt = $pdo->prepare('
        UPDATE rank_tokens
        SET used_tokens = :used_tokens
        WHERE user_id = :user_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':used_tokens', $used_tokens, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update your used rank token count.');
    }

    return $result;
}
