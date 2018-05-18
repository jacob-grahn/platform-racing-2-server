<?php

function guild_transfer_insert($pdo, $user_id, $old_owner, $new_owner, $code, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO guild_transfers
                SET guild_id = :guild_id,
                    old_owner_id = :old_owner,
                    new_owner_id = :new_owner,
                    code = :code,
                    request_ip = :ip,
                    status = "pending",
                    date = NOW()
    ');
    $stmt->bindValue(':guild_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':old_owner', $old_owner, PDO::PARAM_INT);
    $stmt->bindValue(':new_owner', $new_owner, PDO::PARAM_INT);
    $stmt->bindValue(':code', $code, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not start guild transfer.');
    }
    
    return $result;
}
