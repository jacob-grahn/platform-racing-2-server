<?php

function guild_transfers_select_by_guild($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT transfer_id, old_owner_id, new_owner_id, code, date, request_ip, confirm_ip, status
          FROM guild_transfers
         WHERE guild_id = :guild_id
           AND status = "complete";
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not fetch guild transfer history');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
