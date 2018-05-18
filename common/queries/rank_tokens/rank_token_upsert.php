<?php

function rank_token_upsert($pdo, $user_id, $token_count)
{
    $stmt = $pdo->prepare('
                    INSERT INTO rank_tokens
                            SET user_id = :user_id,
                                available_tokens = :token_count
        ON DUPLICATE KEY UPDATE available_tokens = :token_count
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':token_count', $token_count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('rank_token_upsert error');
    }

    return $result;
}
