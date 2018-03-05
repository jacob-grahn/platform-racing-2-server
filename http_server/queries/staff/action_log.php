<?php

function log_mod_action($pdo, $string, $user_id, $ip) {
	$time = (int) time();

	$stmt = $pdo->prepare('INSERT INTO mod_action (string, user_id, ip, time)
					   VALUES (:string, :user_id, :ip, :time)');

	// bind the values
	$stmt->bindValue(':string', $string, PDO::PARAM_STR);
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
	$stmt->bindValue(':time', $time, PDO::PARAM_INT);
	
	// execute the PDO
	$stmt->execute();
}

function log_admin_action($pdo, $string, $user_id, $ip) {
	$time = (int) time();

	$stmt = $pdo->prepare('INSERT INTO admin_action (string, user_id, ip, time)
						 VALUES (:string, :user_id, :ip, :time)');

	// bind the values
	$stmt->bindValue(':string', $string, PDO::PARAM_STR);
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
	$stmt->bindValue(':time', $time, PDO::PARAM_INT);
	
	// execute the PDO
	$stmt->execute();
}

?>
