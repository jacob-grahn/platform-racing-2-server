<?php

function retrieve_ban_list($pdo, $start, $count) {
	$stmt = $pdo->prepare('SELECT * FROM bans ORDER BY time DESC LIMIT ?, ?');
	$stmt->bindValue(1, (int) $start, PDO::PARAM_INT);
	$stmt->bindValue(2, (int) $count, PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $result;
}

?>
