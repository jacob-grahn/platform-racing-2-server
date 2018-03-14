<?php

function rank_token_rentals_delete_old($pdo)
{
	$result = $pdo->exec('
        DELETE FROM rank_token_rentals
        WHERE date < DATE_SUB( NOW(), INTERVAL 1 WEEK )
    ');

    if ($result === false) {
        throw new Exception('could not delete expired rank_token_rentals');
    }

    return $result;
}
