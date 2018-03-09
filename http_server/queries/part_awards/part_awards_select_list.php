<?php

function part_awards_select_list($pdo)
{
	$stmt = $pdo->prepare('
        SELECT user_id, type, part
        FROM part_awards
    ');
	$stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (!$result) {
        throw new Exception('could not fetch part award list');
    }

    return $result;
}
