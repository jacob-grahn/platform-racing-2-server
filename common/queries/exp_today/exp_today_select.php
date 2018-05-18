<?php

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
