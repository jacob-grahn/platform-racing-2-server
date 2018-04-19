<?php

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
