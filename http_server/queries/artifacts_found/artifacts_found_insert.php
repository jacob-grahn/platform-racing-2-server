<?php

// time here is actually a datetime field, not a unix timestamp

function artifacts_found_insert($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO artifacts_found
        SET user_id = :user_id,
    		artifacts = 1,
    		time = NOW()
        ON DUPLICATE KEY UPDATE
        	artifacts = artifacts + 1,
        	time = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not insert found artifact.');
    }

    return $result;
}
