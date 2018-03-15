<?php

function servers_delete_old($pdo)
{
	$stmt = $pdo->prepare('
        DELETE FROM servers
        WHERE expire_date < DATE_SUB( NOW(), INTERVAL 1 MONTH )
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('could delete old servers');
    }

    return $result;
}
