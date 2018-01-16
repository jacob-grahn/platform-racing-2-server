<?php

require_once('../fns/all_fns.php');

try {
	// connect
	$db = new DB();

	// get a list of random levels
	$results = $db->call('levels_select_by_rand');
  $rows = $db->to_array($results);
  echo json_encode($rows);
}

catch(Exception $e){
	echo 'error=' . urlencode($e->getMessage());
}
