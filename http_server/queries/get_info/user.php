<?php

function user_select_by_name($pdo, $name) 
{
    $stmt = $pdo->prepare(
        'SELECT 
		user_id, name, email, register_ip, ip, time, register_time, power, status, server_id, read_message_id, guild, register_date, active_date 
		FROM users WHERE name = :name'
    );
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    
    // get number of results
    $row_count = $stmt->rowCount();
    if ($row_count < 1) {
        return false;
    }
    if ($row_count > 1) {
        // critical failure
    }
    
    // return result
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $result;
}

function user_select_by_id($pdo, $user_id) 
{
    $stmt = $pdo->prepare(
        'SELECT 
		user_id, name, email, register_ip, ip, time, register_time, power, status, server_id, read_message_id, guild, register_date, active_date 
		FROM users WHERE user_id = :id'
    );
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // get number of results
    $row_count = $stmt->rowCount();
    if ($row_count < 1) {
        return false;
    }
    if ($row_count > 1) {
        // critical failure
    }
    
    // return result
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $result;
}

?>
