<?php

function part_awards_delete_old($pdo)
{
    $result = $pdo->exec('DELETE FROM part_awards WHERE DATE_SUB(CURDATE(), INTERVAL 5 DAY) > dateline');

    if ($result === false) {
        throw new Exception('Could not delete old part awards');
    }

    return $result;
}
