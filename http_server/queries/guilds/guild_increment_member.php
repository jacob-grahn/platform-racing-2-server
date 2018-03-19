<?php

function guild_increment_member($pdo, $guild_id, $number)
{
    $stmt = $pdo->prepare('
        UPDATE guilds
           SET member_count = member_count + :number,
         WHERE guild_id = :guild_id
         LIMIT 1
        ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':number', $number, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not increment guild member count.');
    }

    return $result;
}
