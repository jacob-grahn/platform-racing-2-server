<?php

function bans_select_recent($pdo)
{
    $stmt = $pdo->prepare('
        SELECT banned_ip, banned_user_id
        FROM bans
        WHERE time > UNIX_TIMESTAMP( NOW() - INTERVAL 5 MINUTE )
        AND expire_time > UNIX_TIMESTAMP( NOW() )
        AND lifted = 0
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('could not select recent bans');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
