<?php

function level_increment_play_count($pdo, $level_id, $play_count)
{
    $stmt = $pdo->prepare('
        UPDATE pr2_levels
           SET play_count = play_count + :play_count
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':play_count', $play_count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update level #$level_id's play count.");
    }

    return $result;
}
