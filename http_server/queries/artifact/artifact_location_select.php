<?php

function artifact_location_select($pdo)
{
	$stmt = $pdo->prepare('
        SELECT *
        FROM artifact_location
        LIMIT 1
    ');
	$stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        throw new Exception('artifact not found');
    }

    return $result;
}
