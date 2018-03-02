<?php

function user_select_by_name($pdo, $name) {
	$stmt = $pdo->prepare('SELECT * FROM users WHERE name = ?');
	$stmt->bindValue(1, $name, PDO::PARAM_STR);
	$stmt->execute();
	$user = $stmt->fetchAll(PDO::FETCH_OBJ);
	return $user;
}

function user_select_by_id($pdo, $user_id) {
	$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
	$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
	$stmt->execute();
	$user = $stmt->fetchAll(PDO::FETCH_OBJ);
	return $user;
}

?>
