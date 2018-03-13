<?php

function servers_select_list($pdo)
{
	$stmt = $pdo->prepare('
        SELECT *
        FROM servers
        WHERE active = 1
        ORDER BY server_id ASC
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('user pr2 row not found');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);;
}
