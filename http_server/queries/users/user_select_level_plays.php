<?php

function user_select_level_plays($pdo, $user_id)
{
	$stmt = $pdo->prepare('
        SELECT SUM(play_count) as total_play_count
        FROM pr2_levels
        WHERE user_id = :user_id
        GROUP BY user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('error counting level plays');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
	return $row ? $row->total_play_count : 0;
}
