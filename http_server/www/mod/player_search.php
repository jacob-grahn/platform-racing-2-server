<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$message = find('message', '');

try {

	// connect
	$db = new DB();

	// make sure you're a moderator
	$mod = check_moderator($db, false);

	// header
	output_header('Player Search', true);

	//
	if($message != '') {
		echo "<p><b>$message</b></p>";
	}

	?>
	<form action="do_player_search.php" method="get">
		Name <input type="text" value="" name="name" />
		<input type="submit" value="Search" />
	</form>
	<?php

}

catch(Exception $e){
	echo 'Error: '.$e->getMessage();
}

output_footer();
?>
