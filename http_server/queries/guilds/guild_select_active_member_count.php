<?php

function guild_select_active_member_count($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*) AS active_member_count
        FROM users
        WHERE users.guild = :guild_id
        AND users.active_date > DATE_SUB( NOW(), INTERVAL 1 DAY )
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not determine active guild members.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    if ($row === false) {
        return 0;
    }

    return $row->active_member_count;
}
