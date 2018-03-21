<?php

function users_update_status($pdo, $user_id, $status, $server_id)
{
    $stmt = $pdo->prepare('
        UPDATE users
        SET time = UNIX_TIMESTAMP(NOW()),
    		active_date = NOW(),
    		status = p_status,
    		server_id = p_server_id
        WHERE user_id = p_user_id
        LIMIT 1'
    );
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Error updating user status');
    }

    return $result;
}
