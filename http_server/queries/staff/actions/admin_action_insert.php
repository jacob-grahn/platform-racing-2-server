<?php

function admin_action_insert($pdo, $mod_id, $message, $extra, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO admin_actions
           SET time = NOW(),
               mod_id = :mod_id,
               message = :message,
               extra = :extra,
               ip = :ip
    ');
    $stmt->bindValue(':mod_id', $mod_id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $stmt->bindValue(':extra', $extra, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not record admin action.');
    }

    return $result;
}
