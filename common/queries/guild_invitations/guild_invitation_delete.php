<?php

function guild_invitation_delete($pdo, $guild_id, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM guild_invitations
         WHERE guild_id = :guild_id
           AND user_id = :user_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not delete guild invitation.');
    }

    return $result;
}
