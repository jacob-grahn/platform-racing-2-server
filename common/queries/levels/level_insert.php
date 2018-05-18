<?php

function level_insert($pdo, $title, $note, $live, $time, $ip, $min_level, $song, $user_id, $pass, $type)
{
    $stmt = $pdo->prepare('
        INSERT INTO pr2_levels
           SET title = :title,
               note = :note,
               live = :live,
               time = :time,
               ip = :ip,
               min_level = :min_level,
               song = :song,
               user_id = :user_id,
               pass = :pass,
               type = :type
    ');
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $min_level, PDO::PARAM_INT);
    $stmt->bindValue(':song', $song, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not save the new level.');
    }

    return $result;
}
