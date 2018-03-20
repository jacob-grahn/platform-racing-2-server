<?php

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
        throw new Exception('could not check ban by ip');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
