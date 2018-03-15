<?php

function epic_upgrades_select($pdo, $user_id)
{
	$stmt = $pdo->prepare('
        SELECT *
        FROM epic_upgrades
        WHERE user_id = :user_id
        LIMIT 1
    ');
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$result = $stmt->execute();
	if (!$result) {
        throw new Exception('could not fetch from epic_upgrades');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$row) {
        throw new Exception('epic_upgrade row not found');
    }

    return $row;
}
