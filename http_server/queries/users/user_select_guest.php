<?php

function user_select_guest($pdo)
{
    $stmt = $pdo->prepare('
        SELECT user_id, name, email, register_ip, ip, time, register_time, power, status, read_message_id, guild, server_id
        FROM users
        WHERE power = 0
        AND STATUS = "offline"
        LIMIT 1
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not fetch a guest account.');
    }

    $guest = $stmt->fetch(PDO::FETCH_OBJ);
    if ($guest === false) {
        throw new Exception('Guest account not found.');
    }

    return $guest;
}
