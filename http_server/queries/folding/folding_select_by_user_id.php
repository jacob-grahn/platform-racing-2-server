<?php

function folding_select_by_user_id($pdo, $user_id)
{
	$stmt = $pdo->prepare('
        SELECT *
        FROM folding_at_home
        WHERE user_id = :user_id
        LIMIT 1
    ');
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        throw new Exception('folding row not found');
    }

    return $result;
}
