<?php

function throttle_bans($pdo, $mod_user_id) {
	$mod_user_id = (int) $mod_user_id;
	$time = (int) (time() - 3600);
  
	$stmt = $pdo->prepare('SELECT COUNT(*) as recent_ban_count FROM bans WHERE mod_user_id = ? AND time > ?');
	$stmt->bindParam(1, $mod_user_id, PDO::PARAM_INT);
	$stmt->bindParam(1, $mod_user_id, PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetchAll(PDO::FETCH_OBJ);
	return $row;
}

function ban_user($pdo, $banned_ip, $banned_user_id, $mod_user_id, $expire_time, $reason, $record, $banned_name, $mod_name, $ip_ban, $account_ban) {
	$time = (int) time();
	
	// the SQL we want
	$stmt = $pdo->prepare('INSERT INTO bans SET
							banned_ip = ?,
							banned_user_id = ?,
							mod_user_id = ?,
							time = ?,
							expire_time = ?,
							reason = ?,
							record = ?,
							banned_name = ?,
							mod_name = ?,
							ip_ban = ?,
							account_ban = ?');
	
	// bind the parameters
	$stmt->bindParam(1, $banned_ip, PDO::PARAM_STR);
	$stmt->bindParam(2, $banned_user_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $mod_user_id, PDO::PARAM_INT);
	$stmt->bindParam(4, $time, PDO::PARAM_INT);
	$stmt->bindParam(5, $expire_time, PDO::PARAM_INT);
	$stmt->bindParam(6, $reason, PDO::PARAM_STR);
	$stmt->bindParam(7, $record, PDO::PARAM_STR);
	$stmt->bindParam(8, $banned_name, PDO::PARAM_STR);
	$stmt->bindParam(9, $mod_name, PDO::PARAM_STR);
	$stmt->bindParam(10, $ip_ban, PDO::PARAM_INT);
	$stmt->bindParam(11, $account_ban, PDO::PARAM_INT);
	
	// execute the PDO and get the results
	$stmt->execute();
	
	return true;

}

?>
