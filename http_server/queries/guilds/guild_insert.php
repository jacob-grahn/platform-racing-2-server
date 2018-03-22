<?php

function guild_insert($pdo, $owner_id, $guild_name, $emblem, $note)
{
    $stmt = $pdo->prepare('
        INSERT INTO guilds
        SET owner_id = :owner_id,
            guild_name = :guild_name,
            emblem = :emblem,
            note = :note,
            creation_date = NOW(),
            active_date = NOW(),
            member_count = 1
    ');
    $stmt->bindValue(':owner_id', $owner_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_name', $guild_name, PDO::PARAM_STR);
    $stmt->bindValue(':emblem', $emblem, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not create guild.');
    }

    return $pdo->lastInsertId();
}
