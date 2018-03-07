<?php

function pr2_select_by_id($pdo, $id)
{
	$stmt = $pdo->prepare('SELECT * FROM pr2 WHERE user_id = :id');
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
	
	// get number of results
	$row_count = $stmt->rowCount();
	if ($row_count < 1) {
		return false;
	}
	if ($row_count > 1) {
		// log critical failure
		return false;
	}
	
	// return result
	$result = $stmt->fetchAll(PDO::FETCH_OBJ);
	return $result;
}
