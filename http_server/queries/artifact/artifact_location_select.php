<?php

function artifact_location_select($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM artifact_location LIMIT 1');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if ($result == false) {
        throw new Exception('Could not retrieve artifact location.');
    }

    return $result;
}
