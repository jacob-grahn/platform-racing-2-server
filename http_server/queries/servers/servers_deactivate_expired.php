<?php

function servers_deactivate_expired($pdo)
{
	$stmt = $pdo->prepare('
		UPDATE servers
		SET active = 0,
			status = "offline"
		WHERE expire_date < NOW()
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('could deactivate expired servers');
    }

    return $result;
}
