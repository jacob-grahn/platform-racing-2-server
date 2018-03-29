<?php

function changing_email_select($pdo, $code)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM changing_emails
         WHERE code = :code
           AND status = "pending"
         LIMIT 1
    ');
    $stmt->bindValue(':code', $code, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query changing_email_select.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($row)) {
        throw new Exception('Could not find a pending email change with that code.');
    }

    return $row;
}
