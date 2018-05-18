<?php

function ban_lift($pdo, $ban_id, $lifted_by, $lifted_reason)
{
    $stmt = $pdo->prepare('
        UPDATE bans
        SET lifted = "1",
            lifted_by = :lifted_by,
            lifted_reason = :lifted_reason
        WHERE ban_id = :ban_id
        LIMIT 1
    ');
    $stmt->bindValue(':ban_id', $ban_id, PDO::PARAM_INT);
    $stmt->bindValue(':lifted_by', $lifted_by, PDO::PARAM_STR);
    $stmt->bindValue(':lifted_reason', $lifted_reason, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        $ban_id = (int) $ban_id;
        throw new Exception("Could not lift ban #$ban_id.");
    }

    return $result;
}
