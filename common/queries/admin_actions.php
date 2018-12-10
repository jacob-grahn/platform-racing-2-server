<?php

function admin_action_insert($pdo, $mod_id, $message, $extra, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO admin_actions
           SET time = NOW(),
               mod_id = :mod_id,
               message = :message,
               extra = :extra,
               ip = :ip
    ');
    $stmt->bindValue(':mod_id', $mod_id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $stmt->bindValue(':extra', $extra, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record admin action.');
    }

    return $result;
}


function admin_actions_select($pdo, $in_start, $in_count)
{
    $start = max((int) $in_start, 0);
    $count = min(max((int) $in_count, 0), 100);

    $stmt = $pdo->prepare('
          SELECT *
            FROM admin_actions
           ORDER BY time DESC
           LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve the admin action log.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
