<?php

function log_mod_action($pdo, $string, $user_id, $ip) {
	$time = (int) time();

	$stmt = $pdo->prepare('INSERT INTO mod_action SET
								string = ?,
								user_id = ?,
								ip = ?,
								time = ?');

	// bind the parameters
	$stmt->bindParam(1, $string, PDO::PARAM_STR);
	$stmt->bindParam(2, $user_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $ip, PDO::PARAM_STR);
	$stmt->bindParam(4, $time, PDO::PARAM_INT);
	
	// execute the PDO
	$stmt->execute();
	
}

function log_admin_action($pdo, $string, $user_id, $ip) {
	$time = (int) time();

	$stmt = $pdo->prepare('INSERT INTO admin_action SET
								string = ?,
								user_id = ?,
								ip = ?,
								time = ?');

	// bind the parameters
	$stmt->bindParam(1, $string, PDO::PARAM_STR);
	$stmt->bindParam(2, $user_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $ip, PDO::PARAM_STR);
	$stmt->bindParam(4, $time, PDO::PARAM_INT);
	
	// execute the PDO
	$stmt->execute();
	
}

?>
