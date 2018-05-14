<?php

function level_backup_select($pdo, $backup_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM level_backups
        WHERE backup_id = :backup_id
        LIMIT 1
    ');
    $stmt->bindValue(':backup_id', $backup_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query level_backup_select.');
    }

    $backup = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($backup)) {
        throw new Exception('Could not find a level backup with that ID.');
    }

    return $backup;
}
