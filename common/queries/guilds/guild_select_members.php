<?php

// TODO: $suppress_error doesn't make sense here? An error will only be thrown if there is a syntax error with the query

function guild_select_members($pdo, $guild_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT users.user_id, users.name, users.power, pr2.rank, gp.gp_today, gp.gp_total
        FROM users LEFT JOIN pr2 ON users.user_id = pr2.user_id
        LEFT JOIN gp ON (users.user_id = gp.user_id AND gp.guild_id = :guild_id)
        WHERE users.guild = :guild_id
        ORDER BY gp.gp_today DESC
        LIMIT 0, 101
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error = false) {
            throw new Exception('Could not select guild members.');
        } else {
            return false;
        }
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
