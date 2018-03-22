<?php

function throttle_bans($pdo, $mod_user_id)
{
    $throttle_time = (int) (time() - 3600);
  
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as recent_ban_count
	  FROM bans
	 WHERE mod_user_id = :mod
	   AND time > $throttle_time
    ");
    $stmt->bindValue(':mod', $mod_user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not query ban throttle.");
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
