<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');
require_once('mod_fns.php');

$start = find('start', 0);
$count = find('count', 25);

output_mod_header('Action Log');


try {
	
	//connect
	$db = new DB();


	//make sure you're a moderator
	$mod = check_moderator($db, false);
	
	
	//get actions for this page
	$actions = $db->call( 'mod_actions_select', array( $start, $count ) );
	
	
	//navigation
	output_mod_navigation();
	output_pagination($start, $count);
	echo('<p>---</p>');
	
	
	//output actions
	while( $row = $actions->fetch_object() ) {
		//$formatted_time = date('M j, Y g:i A', $row->time);
		echo("<p><span class='date'>$row->time</span> -- ".htmlspecialchars($row->message)."</p>");
	}
	
	
	echo('<p>---</p>');
	output_pagination($start, $count);
	output_footer();
}

catch(Exception $e){
	echo 'Error: '.$e->getMessage();
}

?>