<?php

function part_awards_delete_old($pdo)
{
	$stmt = $pdo->prepare('
        DELETE FROM part_awards
        WHERE DATE_SUB(CURDATE(), INTERVAL 5 DAY) > dateline
    ');
	$result = $stmt->execute();

	if (!$result) {
		throw new Exception('could not delete old part awards');
	}

	return $result;
}
