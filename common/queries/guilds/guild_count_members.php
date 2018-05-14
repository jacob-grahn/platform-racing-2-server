<?php

function guild_count_members($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
          FROM users
         WHERE guild = :guild_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not count the number of members in guild $guild_id.");
    }

    $count = $stmt->fetchColumn();
    
    if ((int) $count == 0 || $count == false || empty($count)) {
        return 0;
    }
    
    return $count;
}
