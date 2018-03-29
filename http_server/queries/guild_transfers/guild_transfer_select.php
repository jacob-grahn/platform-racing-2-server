<?php

function guild_transfer_select($pdo, $code)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM guild_transfers
         WHERE code = :code
           AND status = "pending"
         LIMIT 1
    ');
    $stmt->bindValue(':code', $code, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could fetch guild transfer request.');
    }
    
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($row)) {
        throw new Exception('No pending guild transfer found for that code.');
    }
    
    return $row;
}
