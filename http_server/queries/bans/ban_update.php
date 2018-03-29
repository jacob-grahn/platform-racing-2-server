<?php

function ban_update($pdo, $ban_id, $account_ban, $ip_ban, $expire_time, $notes)
{
    $stmt = $pdo->prepare('
        UPDATE bans
        SET account_ban = :account_ban,
            ip_ban = :ip_ban,
            expire_time = UNIX_TIMESTAMP(:expire_time),
            notes = :notes,
            modified_time = NOW()
        WHERE ban_id = :ban_id
        LIMIT 1
    ');
    $stmt->bindValue(':ban_id', $ban_id, PDO::PARAM_INT);
    $stmt->bindValue(':account_ban', $account_ban, PDO::PARAM_INT);
    $stmt->bindValue(':ip_ban', $ip_ban, PDO::PARAM_INT);
    $stmt->bindValue(':expire_time', $expire_time, PDO::PARAM_STR);
    $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update ban #$ban_id.");
    }

    return $result;
}
