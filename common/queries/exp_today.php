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


function exp_today_select($pdo, $look)
{
    $stmt = $pdo->prepare('
        SELECT exp
          FROM exp_today
         WHERE look = :look
         LIMIT 1
    ');
    $stmt->bindValue(':look', $look, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query exp_today_select.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        return 0;
    }

    return $row->exp;
}


function exp_today_truncate($pdo)
{
    $result = $pdo->exec('TRUNCATE TABLE exp_today');

    if ($result === false) {
        throw new Exception('Could not truncate table exp_today.');
    }

    return $result;
}
