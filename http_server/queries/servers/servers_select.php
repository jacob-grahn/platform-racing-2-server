<?php

function servers_select($pdo)
{
	$stmt = $pdo->prepare('
        SELECT *
        FROM servers
        WHERE active = 1
        ORDER BY server_id ASC
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('could not select active servers');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
