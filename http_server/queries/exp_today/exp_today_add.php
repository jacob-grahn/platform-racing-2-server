<?php

function exp_today_add($pdo, $look, $exp)
{
    $stmt = $pdo->prepare('
        INSERT INTO exp_today
        SET look = :look,
            exp = :exp
        ON DUPLICATE KEY UPDATE
            exp = exp + :exp
    ');
    $stmt->bindValue(':look', $look, PDO::PARAM_STR);
    $stmt->bindValue(':exp', $exp, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not save your exp today.');
    }

    return $result;
}
