<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$name = default_val($_POST['name'], '');
$ip = get_ip();

try {
	
	// POST check
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception("Invalid request method.");
	}
	
	// sanity check
	if(is_empty($name)) {
		throw new Exception('No username specified.');
	}
	
	// rate limiting
	rate_limit('mod-do-player-search-'.$ip, 10, 1);
	rate_limit('mod-do-player-search-'.$ip, 60, 5);

	// connect
	$db = new DB();

	// make sure you're a moderator
	$mod = check_moderator($db);
	
}
catch(Exception $e) {
	$error = $e->getMessage();
	output_header("Error");
	echo "Error: $error";
	output_footer();
}

try {
	
	// more rate limiting
	$mod_id = $mod->user_id;
	rate_limit('mod-do-player-search-'.$mod_id, 10, 1);
	rate_limit('mod-do-player-search-'.$mod_id, 60, 5);

	// look for a player with provided name
	$user_id = name_to_id($db, $name);

	// redirect
	header("Location: player_info.php?user_id=$user_id");
	die();
	
}

catch(Exception $e){
	$error = urlencode($e->getMessage());
	header("Location: player_search.php?message=$error");
	die();
}

?>
