<?php

function guild_increment_member($pdo, $guild_id, $number, $suppress_error = false)
{
    $number = (int) $number;
    
    // determine correct operation
    if ($number < 0) {
        $number = abs($number);
        $sign = '-';
    } else if ($number > 0) {
        $sign = '+';
    } else {
        return true;
    }
    
    $stmt = $pdo->prepare("
        UPDATE guilds
           SET member_count = member_count $sign :number,
         WHERE guild_id = :guild_id
         LIMIT 1
    ");
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':number', $number, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === true) {
            return false;
        } else {
            throw new Exception('Could not increment guild member count.');
        }
    }

    return $result;
}
