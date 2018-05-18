<?php

function gp_reset($pdo)
{
    $result = $pdo->exec('UPDATE gp SET gp_today = 0');

    if ($result === false) {
        throw new Exception('Could not reset column gp_today.');
    }

    return $result;
}
