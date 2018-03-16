<?php

function guilds_reset_gp_today($pdo)
{
    $result = $pdo->exec('UPDATE guilds SET gp_today = 0');

    if ($result === false) {
        throw new Exception('Could not reset guilds gp_today');
    }

    return $result;
}
