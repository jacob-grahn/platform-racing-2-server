<?php

function pr2_select($pdo, $user_id)
{
	$stmt = $pdo->prepare('
        SELECT *
        FROM pr2
        WHERE user_id = :user_id
        LIMIT 1
    ');
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        throw new Exception('user pr2 row not found');
    }

    return $result;
}
