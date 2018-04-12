<?php

function purchases_select_recent($pdo)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM purchases
        WHERE date > DATE_SUB( NOW(), INTERVAL 1 HOUR )
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select recent purchases.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
