<?php


function folding_insert($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO folding_at_home
        SET user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not create a folding_at_home row for user #$user_id.");
    }

    return $result;
}


function folding_select_by_user_id($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT fah.*,
               u.name,
               u.status,
               p.hat_array,
               IFNULL(eu.epic_hats, "") as epic_hats,
               IFNULL(rt.available_tokens, 0) as available_tokens
          FROM folding_at_home fah, users u
          LEFT JOIN pr2 p ON u.user_id = p.user_id
          LEFT JOIN epic_upgrades eu ON u.user_id = eu.user_id
          LEFT JOIN rank_tokens rt ON u.user_id = rt.user_id
         WHERE fah.user_id = u.user_id
           AND u.user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query folding_select_by_user_id.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        if ($suppress_error === false) {
            throw new Exception("Could not find a folding_at_home entry for user #$user_id.");
        } else {
            return null;
        }
    }

    return $row;
}


function folding_select_list($pdo)
{
    $stmt = $pdo->prepare('
        SELECT fah.*,
               u.name,
               u.status,
               p.hat_array,
               IFNULL(eu.epic_hats, "") as epic_hats,
               IFNULL(rt.available_tokens, 0) as available_tokens
          FROM folding_at_home fah, users u
          LEFT JOIN pr2 p ON u.user_id = p.user_id
          LEFT JOIN epic_upgrades eu ON u.user_id = eu.user_id
          LEFT JOIN rank_tokens rt ON u.user_id = rt.user_id
         WHERE fah.user_id = u.user_id
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query folding_select_list.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function folding_update($pdo, $user_id, $column_name)
{
    $columns = ['r1', 'r2', 'r3', 'r4', 'r5', 'r6', 'r7', 'r8', 'crown_hat', 'epic_crown', 'cowboy_hat', 'epic_cowboy'];
    if (array_search($column_name, $columns) === false) {
        throw new Exception('Invalid column name in folding_update');
    }

    $stmt = $pdo->prepare("
        UPDATE folding_at_home
           SET $column_name = 1
         WHERE user_id = :user_id
         LIMIT 1
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update folding_at_home entry for user #$user_id at column $column_name.");
    }

    return $result;
}
