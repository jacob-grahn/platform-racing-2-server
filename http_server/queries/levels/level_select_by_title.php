<?php

// TO-DO: Is this needed?
function level_select_by_title($pdo, $user_id, $title)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM pr2_levels
         WHERE user_id = :user_id
           AND title = :title
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not fetch level by title.');
    }

    $level = $stmt->fetch(PDO::FETCH_OBJ);
    return $level;
}
