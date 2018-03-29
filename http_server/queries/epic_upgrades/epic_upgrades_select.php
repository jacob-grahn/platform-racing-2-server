<?php

function epic_upgrades_select($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('SELECT * FROM epic_upgrades WHERE user_id = :user_id LIMIT 1');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not retrieve epic upgrades data from the database.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($row)) {
        if ($suppress_error === false) {
            throw new Exception("Could not find epic upgrades data for user #$user_id.");
        } else {
            return false;
        }
    }

    return $row;
}
