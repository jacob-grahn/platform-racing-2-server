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
        throw new Exception('Could fetch email change');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    if ($row === false) {
        throw new Exception('Email change not found');
    }

    return $row;
}
