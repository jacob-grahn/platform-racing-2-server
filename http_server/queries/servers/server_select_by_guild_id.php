<?php

function server_select_by_guild_id($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM servers
        WHERE guild_id = :guild_id
        LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select server for your guild.');
    }

    $server = $stmt->fetch(PDO::FETCH_OBJ);
    /* if (!$server) {
        throw new Exception('Could not find a server with that guild ID.');
    } */

    return $server;
}
