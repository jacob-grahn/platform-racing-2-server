<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$message = default_val($_GET['message'], '');
$ip = get_ip();

try {

	// rate limiting
	rate_limit('mod-player-search-'.$ip, 3, 2);
	
	// connect
	$db = new DB();

	// make sure you're a moderator
	$mod = check_moderator($db, false);
	
}
catch(Exception $e) {
	$error = $e->getMessage();
	output_header("Error");
	echo "Error: $error";
	output_footer();
	die();
}

try {
	
	// header
	output_header('Player Search', true);

	// error message
	if(!is_empty($message)) {
		echo "<p><b>$message</b></p>";
	}

	?>
	<form action="do_player_search.php" method="post">
		Name <input type="text" value="" name="name" />
		<input type="submit" value="Search" />
	</form>
	<?php
	
	// footer
	output_footer();

}

catch(Exception $e){
	$error = $e->getMessage();
	echo "Error: $error";
	output_footer();
}

?>
