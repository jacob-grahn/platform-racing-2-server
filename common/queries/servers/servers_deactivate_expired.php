<?php

function servers_deactivate_expired($pdo)
{
    $result = $pdo->exec('
        UPDATE servers
           SET active = 0,
               status = "offline"
         WHERE expire_date < NOW()
    ');
    
    if ($result === false) {
        throw new Exception('Could not deactivate expired servers');
    }

    return $result;
}
