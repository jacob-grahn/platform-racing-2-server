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


function mod_power_insert($pdo, $user_id, $max_ban, $bans_per_hour, $can_unpublish_level)
{
    $stmt = $pdo->prepare('
        INSERT INTO mod_power
        SET user_id = :user_id,
            max_ban = :max_ban,
            bans_per_hour = :bans_per_hour,
            can_ban_ip = 1,
            can_ban_account = 1,
            can_unpublish_level = :can_unpublish_level
        ON DUPLICATE KEY UPDATE
            max_ban = :max_ban,
            bans_per_hour = :bans_per_hour,
            can_ban_ip = 1,
            can_ban_account = 1,
            can_unpublish_level = :can_unpublish_level
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':max_ban', $max_ban, PDO::PARAM_INT);
    $stmt->bindValue(':bans_per_hour', $bans_per_hour, PDO::PARAM_INT);
    $stmt->bindValue(':can_unpublish_level', $can_unpublish_level, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query mod_power_insert.');
    }

    return $result;
}


function mod_power_select($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM mod_power
         WHERE user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query mod_power_select.');
    }

    $mod = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($mod)) {
        if ($suppress_error === false) {
            throw new Exception('Could not find mod power data for this user.');
        } else {
            return false;
        }
    }

    return $mod;
}
