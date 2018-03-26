<?php

// TO-DO: is this needed?
function user_select_level_plays($pdo, $user_id)
{
    $stmt = $pdo->prepare('
          SELECT SUM(play_count) as total_play_count
            FROM pr2_levels
           WHERE user_id = :user_id
        GROUP BY user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not count the number of plays for this user.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    return $row ? $row->total_play_count : 0;
}
