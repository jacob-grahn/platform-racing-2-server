<?php

function guild_delete($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM guilds
        WHERE guild_id = :guild_id
        LIMIT 1;

        UPDATE users
        SET guild = 0
        WHERE guild = :guild_id;

        DELETE FROM gp
        WHERE guild_id = :guild_id;
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not delete guild');
    }

    return $result;
}
