<?php


function token_delete($pdo, $token, $ret_count = false)
{
    $stmt = $pdo->prepare('
        DELETE FROM tokens
         WHERE token = :token
    ');
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete your login token from the database.');
    }

    return $ret_count === true ? $stmt->rowCount() > 0 : $result;
}


function token_insert($pdo, $user_id, $token)
{
    $stmt = $pdo->prepare('
        INSERT INTO tokens
           SET user_id = :user_id,
               token = :token,
               time = NOW()
        ON DUPLICATE KEY UPDATE
               token = :token,
               time = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not create a login token for you.');
    }

    return $result;
}


function token_select($pdo, $token_id)
{
    $stmt = $pdo->prepare('
        SELECT user_id, token
          FROM tokens
         WHERE token = :token_id
         LIMIT 1
    ');
    $stmt->bindValue(':token_id', $token_id, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query token_select.');
    }

    $token = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($token)) {
        throw new Exception('Could not find a valid login token. Please log in again.');
    }

    return $token;
}


function tokens_delete_by_user($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM tokens
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete this user\'s login tokens.');
    }

    return $result;
}


function tokens_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM tokens
        WHERE Date(time) < DATE_SUB(NOW(), INTERVAL 1 MONTH)
    ');

    if ($result === false) {
        throw new Exception('Could not delete expired login tokens.');
    }

    return $result;
}
