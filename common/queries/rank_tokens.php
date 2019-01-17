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


function rank_token_upsert($pdo, $user_id, $token_count)
{
    $stmt = $pdo->prepare('
        INSERT INTO
          rank_tokens
        SET
          user_id = :user_id,
          available_tokens = :token_count
        ON DUPLICATE KEY UPDATE
          available_tokens = :token_count
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':token_count', $token_count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('rank_token_upsert error');
    }

    return $result;
}
