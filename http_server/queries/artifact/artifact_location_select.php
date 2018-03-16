<?php

function artifact_location_select($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM artifact_location LIMIT 1');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve artifact location.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
