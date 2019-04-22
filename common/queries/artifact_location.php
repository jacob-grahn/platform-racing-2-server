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


function artifact_location_update_bubbles_winner($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
        SET bubbles_winner = :user_id
        WHERE artifact_id = 1
        AND bubbles_winner = 0
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update artifact first finder.');
    }

    return $result;
}


function artifact_location_update_first_finder($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
        SET first_finder = :user_id
        WHERE artifact_id = 1
        AND first_finder = 0
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update artifact first finder.');
    }

    return $result;
}


function artifact_location_update($pdo, $level_id, $x, $y)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
           SET level_id = :level_id,
               x = :x,
               y = :y,
               updated_time = NOW(),
               first_finder = 0,
               bubbles_winner = 0
         WHERE artifact_id = 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':x', $x, PDO::PARAM_INT);
    $stmt->bindValue(':y', $y, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update artifact location.');
    }

    return $result;
}
