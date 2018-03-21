<?php

function token_delete ($pdo, $token)
{
    $stmt = $pdo->prepare('
        DECLARE _user_id INT(11);

        SELECT user_id INTO _user_id
        FROM tokens
        WHERE token = :token;

        DELETE FROM tokens
        WHERE user_id = _user_id;

        SELECT _user_id;
    ');
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not delete login token');
    }

    return $result;
}
