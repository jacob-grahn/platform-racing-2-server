<?php

function log_mod_action($pdo, $string, $user_id, $ip) {
	$time = (int) time();

	$stmt = $pdo->prepare('INSERT INTO mod_action (string, user_id, ip, time) VALUES (?, ?, ?, ?)');

	// bind the values
	$stmt->bindValue(1, $string, PDO::PARAM_STR);
	$stmt->bindValue(2, $user_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $ip, PDO::PARAM_STR);
	$stmt->bindValue(4, $time, PDO::PARAM_INT);
	
	// execute the PDO
	$stmt->execute();
}

function log_admin_action($pdo, $string, $user_id, $ip) {
	$time = (int) time();

	$stmt = $pdo->prepare('INSERT INTO admin_action (string, user_id, ip, time) VALUES (?, ?, ?, ?)');

	// bind the values
	$stmt->bindValue(1, $string, PDO::PARAM_STR);
	$stmt->bindValue(2, $user_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $ip, PDO::PARAM_STR);
	$stmt->bindValue(4, $time, PDO::PARAM_INT);
	
	// execute the PDO
	$stmt->execute();
}

?>
