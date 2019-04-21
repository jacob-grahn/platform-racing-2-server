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


function artifacts_found_select_time($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT UNIX_TIMESTAMP(time) as timestamp
        FROM artifacts_found
        WHERE user_id = :user_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select your last found artifact.');
    }

    return (int) $stmt->fetchColumn();
}


function artifacts_found_select_first_count($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT artifacts_first
        FROM artifacts_found
        WHERE user_id = :user_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select artifacts found count.');
    }

    return (int) $stmt->fetchColumn();
}


function artifacts_found_increment_first_count($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        UPDATE artifacts_found
        SET artifacts_first = artifacts_first + 1
        WHERE user_id = :user_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not increment artifacts found first count.');
    }

    return $result;
}
