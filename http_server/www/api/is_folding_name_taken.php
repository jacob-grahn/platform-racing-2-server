<?php

require_once('../../fns/all_fns.php');

try {
	$name = find('name');
	$name = addslashes($name);
	$is_taken = 0;

	$db = new DB();

	try {
		$user_id = name_to_id($db, $name);
		$safe_user_id = addslashes($user_id);
	}
	catch(Exception $e) {
		$is_taken = 0;
	}

	if(isset($user_id)) {
		$result = $db->query('
			SELECT user_id
			FROM folding_at_home
			WHERE user_id = '.$safe_user_id.'
			LIMIT 0, 1
		');

		if(!$result) {
			throw new Exception('Could not check if folding account exists.');
		}

		if($result->num_rows > 0) {
			$is_taken = 1;
		}
		else {
			$is_taken = 0;
		}
	}

	echo 'result='.$is_taken;
}

catch(Exception $e) {
	echo 'error='.($e->getMessage());
}

?>
