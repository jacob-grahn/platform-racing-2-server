<?php

function user_select_expanded($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT pr2.*,
               epic_upgrades.epic_hats,
               epic_upgrades.epic_heads,
               epic_upgrades.epic_bodies,
               epic_upgrades.epic_feet,
               users.name,
               users.power,
               users.status,
               users.time,
               users.register_time,
               users.guild,
               rank_tokens.used_tokens,
               users.server_id
          FROM users
          LEFT JOIN pr2 ON users.user_id = pr2.user_id
          LEFT JOIN rank_tokens ON rank_tokens.user_id = pr2.user_id
          LEFT JOIN epic_upgrades ON users.user_id = epic_upgrades.user_id
         WHERE users.user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query user_select_expanded.');
    }

    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($user)) {
        if ($suppress_error === false) {
            throw new Exception('user_select_expanded: Could not find a user with that ID.');
        } else {
            return false;
        }
    }

    return $user;
}
