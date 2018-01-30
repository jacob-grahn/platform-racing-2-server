<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');
require_once('mod_fns.php');

$start = find('start', 0);
$count = find('count', 25);

try {

	//connect
	$db = new DB();

	//make sure you're a moderator
	$mod = check_moderator($db, false);

	//get actions for this page
	$actions = $db->call( 'mod_actions_select', array( $start, $count ) );

	// output header
	output_header('Mod Action Log', true);

	//navigation
	output_pagination($start, $count);
	echo('<p>---</p>');

	//output actions
	while( $row = $actions->fetch_object() ) {
		echo("<p><span class='date'>$row->time</span> -- ".htmlspecialchars($row->message)."</p>");
	}

	echo('<p>---</p>');
	output_pagination($start, $count);
	output_footer();
}

catch(Exception $e){
	output_header('Error');
	echo 'Error: '.$e->getMessage();
	output_footer();
}

?>
