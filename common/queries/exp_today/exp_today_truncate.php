<?php

function exp_today_truncate($pdo)
{
    $result = $pdo->exec('TRUNCATE TABLE exp_today');

    if ($result === false) {
        throw new Exception('Could not truncate table exp_today.');
    }

    return $result;
}
