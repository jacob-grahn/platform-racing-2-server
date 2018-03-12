<?php

function folding_select_list($pdo)
{
	$stmt = $pdo->prepare('
        SELECT folding_at_home.*, users.name, users.status
        FROM folding_at_home, users
        WHERE folding_at_home.user_id = users.user_id
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('folding query error');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
