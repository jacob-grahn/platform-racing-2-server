<?php

function level_update($pdo, $level_id, $title, $note, $live, $time, $ip, $min_level, $song, $version, $pass, $type)
{
    $stmt = $pdo->prepare('
        UPDATE pr2_levels
           SET title = :title,
               note = :note,
               live = :live,
               time = :time,
               ip = :ip,
               min_level = :min_level,
               song = :song,
               version = :version,
               pass = :pass,
               type = :type
         WHERE level_id = :level_id
         LIMIT 1;
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_STR);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $min_level, PDO::PARAM_INT);
    $stmt->bindValue(':song', $song, PDO::PARAM_INT);
    $stmt->bindValue(':version', $version, PDO::PARAM_INT);
    $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not update level.');
    }

    return $result;
}
