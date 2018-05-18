<?php

function mod_power_delete($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM mod_power
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query mod_power_delete.');
    }

    return $result;
}
