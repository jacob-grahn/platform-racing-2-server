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
        SELECT fah.*, u.name, u.status
          FROM folding_at_home fah, users u
         WHERE u.user_id = fah.user_id
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
        SELECT folding_at_home.*, users.name, users.status
          FROM folding_at_home, users
         WHERE folding_at_home.user_id = users.user_id
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query folding_select_list.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function folding_update($pdo, $user_id, $column_name)
{
    $columns = ['r1', 'r2', 'r3', 'r4', 'r5', 'crown_hat', 'cowboy_hat'];
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
