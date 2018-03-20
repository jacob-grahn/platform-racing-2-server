<?php

function ban_select ($pdo, $ban_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE ban_id = :ban_id
        LIMIT 1
    ');
    $stmt->bindValue(':ban_id', $ban_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select ban');
    }

    $ban = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$ban) {
        throw new Exception('Ban not found');
    }

    return $ban;
}
