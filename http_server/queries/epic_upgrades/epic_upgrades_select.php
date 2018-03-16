<?php

function epic_upgrades_select($pdo, $user_id)
{
    $stmt = $pdo->prepare('SELECT * FROM epic_upgrades WHERE user_id = :user_id LIMIT 1');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not get data from epic_upgrades');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    if ($row === false) {
        throw new Exception('Could not find row in epic_upgrades');
    }

    return $row;
}
