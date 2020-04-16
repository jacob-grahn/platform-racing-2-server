<?php


function ban_insert($pdo, $ip, $uid, $mod_uid, $expire_time, $reason, $record, $name, $mod_name, $is_ip, $is_acc)
{
    $stmt = $pdo->prepare('
        INSERT INTO bans
           SET banned_ip = :banned_ip,
               banned_user_id = :banned_user_id,
               mod_user_id = :mod_user_id,
               time = :time,
               expire_time = :expire_time,
               reason = :reason,
               record = :record,
               banned_name = :banned_name,
               mod_name = :mod_name,
               ip_ban = :ip_ban,
               account_ban = :account_ban,
               modified_time = UNIX_TIMESTAMP(NOW())
    ');
    $stmt->bindValue(':banned_ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':banned_user_id', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':mod_user_id', $mod_uid, PDO::PARAM_INT);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':expire_time', $expire_time, PDO::PARAM_INT);
    $stmt->bindValue(':reason', $reason, PDO::PARAM_STR);
    $stmt->bindValue(':record', $record, PDO::PARAM_STR);
    $stmt->bindValue(':banned_name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':mod_name', $mod_name, PDO::PARAM_STR);
    $stmt->bindValue(':ip_ban', $is_ip, PDO::PARAM_INT);
    $stmt->bindValue(':account_ban', $is_acc, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not ban user.');
    }

    return $result;
}


function ban_select_active_by_ip($pdo, $ip)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_ip = :ip
        AND ip_ban = 1
        AND lifted != 1
        AND expire_time > :time
        LIMIT 1
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_INT);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query ban_select_active_by_ip.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function ban_select_active_by_user_id($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_user_id = :user_id
        AND account_ban = 1
        AND lifted != 1
        AND expire_time > :time
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query ban_select_active_by_user_id.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function ban_select($pdo, $ban_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM bans
        WHERE ban_id = :ban_id
        LIMIT 1
    ');
    $stmt->bindValue(':ban_id', $ban_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select ban.');
    }

    $ban = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($ban)) {
        throw new Exception('Could not find a ban with that ID.');
    }

    return $ban;
}


function ban_update($pdo, $ban_id, $acc_ban, $ip_ban, $exp_time, $lifted, $lifted_by, $lift_reason, $lift_time, $notes)
{
    $stmt = $pdo->prepare('
        UPDATE bans
        SET account_ban = :acc_ban,
            ip_ban = :ip_ban,
            expire_time = :exp_time,
            lifted = :lifted,
            lifted_by = :lifted_by,
            lifted_reason = :lift_reason,
            lifted_time = :lift_time,
            notes = :notes,
            modified_time = UNIX_TIMESTAMP(NOW())
        WHERE ban_id = :ban_id
        LIMIT 1
    ');
    $stmt->bindValue(':ban_id', $ban_id, PDO::PARAM_INT);
    $stmt->bindValue(':acc_ban', $acc_ban, PDO::PARAM_INT);
    $stmt->bindValue(':ip_ban', $ip_ban, PDO::PARAM_INT);
    $stmt->bindValue(':exp_time', strtotime($exp_time), PDO::PARAM_STR);
    $stmt->bindValue(':lifted', $lifted, PDO::PARAM_STR);
    $stmt->bindValue(':lifted_by', $lifted_by, PDO::PARAM_STR);
    $stmt->bindValue(':lift_reason', $lift_reason, PDO::PARAM_STR);
    $stmt->bindValue(':lift_time', $lift_time, PDO::PARAM_INT);
    $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update ban #$ban_id.");
    }

    return $result;
}


// alias for ban_insert
function ban_user($pdo, $ip, $uid, $mod_uid, $expire_time, $reason, $record, $name, $mod_name, $is_ip, $is_acc)
{
    ban_insert($pdo, $ip, $uid, $mod_uid, $expire_time, $reason, $record, $name, $mod_name, $is_ip, $is_acc);
}


function bans_delete_old($pdo)
{
    $result = $pdo->exec('DELETE FROM bans WHERE expire_time < UNIX_TIMESTAMP(NOW() - INTERVAL 1 YEAR)');

    if ($result === false) {
        throw new Exception('Could not delete old bans.');
    }

    return $result;
}


function bans_select_by_ip($pdo, $ip)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_ip = :ip
        AND ip_ban = 1
        ORDER BY time DESC
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select bans by IP.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function bans_select_by_user_id($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_user_id = :user_id
        AND account_ban = 1
        ORDER BY time DESC
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select bans by user ID.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function bans_select_recent($pdo)
{
    $stmt = $pdo->prepare('
        SELECT banned_ip, banned_user_id
        FROM bans
        WHERE time > UNIX_TIMESTAMP(NOW() - INTERVAL 5 MINUTE)
        AND expire_time > UNIX_TIMESTAMP(NOW())
        AND lifted = 0
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select recent bans.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function retrieve_ban_list($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        ORDER BY time DESC
        LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve the ban list.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function throttle_bans($pdo, $mod_user_id)
{
    $throttle_time = (int) (time() - 3600);
  
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as recent_ban_count
          FROM bans
         WHERE mod_user_id = :mod
           AND time > $throttle_time
    ");
    $stmt->bindValue(':mod', $mod_user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not query ban throttle.");
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
