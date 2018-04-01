<?php

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
