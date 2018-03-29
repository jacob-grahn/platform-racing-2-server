<?php

function messages_delete_old($pdo)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages
        WHERE time < UNIX_TIMESTAMP(date_sub(NOW(), interval 2 year))
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete old messages.');
    }

    return $result;
}
