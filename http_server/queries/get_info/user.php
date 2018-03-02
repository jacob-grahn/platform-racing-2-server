<?php

function user_select_by_name($pdo, $name) {
	$stmt = $pdo->prepare('SELECT * FROM users WHERE name = ?');
	$stmt->bindValue(1, $name, PDO::PARAM_STR);
	$stmt->execute();
	$row_count = $stmt->rowCount();
	if ($row_count < 1) {
		return false;
	}
	// TODO: Add check for more than 1 result and log critical failure if so.
	$user = $stmt->fetchAll(PDO::FETCH_OBJ);
	return $user;
}

function user_select_by_id($pdo, $user_id) {
	$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
	$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
	$stmt->execute();
	$row_count = $stmt->rowCount();
	if ($row_count < 1) {
		return false;
	}
	// TODO: Add check for more than 1 result and log critical failure if so.
	$user = $stmt->fetchAll(PDO::FETCH_OBJ);
	return $user;
}

?>
