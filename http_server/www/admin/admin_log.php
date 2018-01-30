<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');
require_once('../../www/mod/mod_fns.php');

$start = find('start', 0);
$count = find('count', 25);

try {

	//connect
	$db = new DB();
  
	//make sure you're an admin
	$admin = check_moderator($db, true, 3);

	//get actions for this page
	$actions = $db->call( 'admin_actions_select', array( $start, $count ) );

	// output header
	output_header('Admin Action Log', true, true);

	//navigation
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
	output_header('Error');
	echo 'Error: '.$e->getMessage();
	output_footer();
}

?>
