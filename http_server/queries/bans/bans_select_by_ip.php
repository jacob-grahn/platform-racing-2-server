<?php

function bans_count_by_ip($pdo, $ip)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_ip = :ip
        AND ip_ban = 1
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('could not select bans by ip');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
