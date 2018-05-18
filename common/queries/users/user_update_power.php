<?php

function user_update_power($pdo, $user_id, $power)
{
    $stmt = $pdo->prepare('
        UPDATE users
        SET power = :power
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':power', $power, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update user power');
    }

    return $result;
}
