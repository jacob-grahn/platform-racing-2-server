<?php

function retrieve_ban_list($pdo, $start, $count)
{
    $stmt = $pdo->prepare('SELECT * FROM bans ORDER BY time DESC LIMIT :start, :count');
    $stmt->bindValue(':start', (int) $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', (int) $count, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
