<?php

function level_backup_select ($pdo, $backup_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM level_backups
        WHERE backup_id = :backup_id
        LIMIT 0, 1
    ');
    $stmt->bindValue(':backup_id', $backup_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could fetch level backup');
    }

    $backup = $stmt->fetch(PDO::FETCH_OBJ);
    if ($backup === false) {
        throw new Exception('Backup not found');
    }

    return $backup;
}
