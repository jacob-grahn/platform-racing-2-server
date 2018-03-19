<?php

function guild_select($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM guilds
        WHERE guild_id = :guild_id
        LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not fetch guild');
    }

    $guild = $stmt->fetch(PDO::FETCH_OBJ);
    if ($guild === false) {
        throw new Exception('Guild not found');
    }

    return $guild;
}
