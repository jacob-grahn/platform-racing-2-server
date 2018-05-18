<?php

function contests_delete_old($pdo)
{
    $yearago = time() - 31536000;
    
    $stmt = $pdo->prepare("
        DELETE FROM contests
        WHERE active = 0
        AND updated < $yearago
    ");
    $result = $stmt->execute();
    
    if ($result === false) {
        return false;
    }
}
