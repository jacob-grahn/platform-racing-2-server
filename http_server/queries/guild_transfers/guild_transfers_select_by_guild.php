<?php

function guild_transfers_select_by_guild($pdo, $guild_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
          SELECT transfer_id, old_owner_id, new_owner_id, code, date, request_ip, confirm_ip, status
            FROM guild_transfers
           WHERE guild_id = :guild_id
        ORDER BY transfer_id ASC
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not fetch guild transfer history.');
    }
    
    $transfers = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($transfers)) {
        if ($suppress_error === false) {
            throw new Exception('Could not find any guild transfers for this guild.');
        } else {
            return false;
        }
    }

    return $transfers;
}
