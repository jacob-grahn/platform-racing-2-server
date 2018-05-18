<?php

function retrieve_ban_list($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        ORDER BY time DESC
        LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve the ban list.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
