<?php

function guild_increment_member($pdo, $guild_id, $number)
{
    $number = (int) $number;
    
    // determine correct operation
    if ($number < 0) {
        $number = abs($number);
        $sign = '-';
    } elseif ($number > 0) {
        $sign = '+';
    } else {
        return true;
    }
    
    $stmt = $pdo->prepare("
        UPDATE guilds
           SET member_count = member_count $sign :number,
               active_date = NOW()
         WHERE guild_id = :guild_id
         LIMIT 1
    ");
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':number', $number, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not increment guild member count in guild $guild_id.");
    }

    return $result;
}
