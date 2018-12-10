<?php


function guild_transfer_complete($pdo, $transfer_id, $ip)
{
    $stmt = $pdo->prepare('
        UPDATE guild_transfers
           SET status = "complete",
               confirm_ip = :ip
         WHERE transfer_id = :transfer_id
    ');
    $stmt->bindValue(':transfer_id', $transfer_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not complete the guild transfer.');
    }

    return $result;
}


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


function guild_transfer_select($pdo, $code)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM guild_transfers
         WHERE code = :code
           AND status = "pending"
         LIMIT 1
    ');
    $stmt->bindValue(':code', $code, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could fetch guild transfer request.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        throw new Exception('No pending guild transfer found for that code.');
    }

    return $row;
}


function guild_transfers_expire_old($pdo)
{
    $result = $pdo->exec('
        UPDATE guild_transfers
           SET status = "expired",
               confirm_ip = "n/a"
         WHERE DATE(date) < DATE_SUB(NOW(), INTERVAL 1 DAY)
           AND status = "pending"
    ');

    if ($result === false) {
        throw new Exception('Could not expire old guild transfers.');
    }

    return $result;
}


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
