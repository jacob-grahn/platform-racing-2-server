<?php

function contests_delete_old($pdo)
{
    $year = time() - 31536000;
    
    $stmt = $pdo->prepare("
        DELETE FROM contests
              WHERE active = 0
                AND updated < $year
    ");
    $stmt->execute();
}
