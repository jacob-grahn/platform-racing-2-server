<?php

function guild_invitation_insert($pdo, $guild_id, $user_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO guild_invitations
           SET guild_id = :guild_id,
               user_id = :user_id,
               date = NOW()
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not create guild invitation.');
    }

    return $result;
}
