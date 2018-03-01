<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');
require_once('mod_fns.php');

$start = (int) default_val($_GET['start'], 0);
$count = (int) default_val($_GET['count'], 25);
$ip = get_ip();

try {

	// rate limiting
	rate_limit('mod-action-log-'.$ip, 5, 2);
	
	//connect
	$db = new DB();

	//make sure you're a moderator
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

	// get actions for this page
	$actions = $db->call('mod_actions_select', array($start, $count));

	// output header
	output_header('Mod Action Log', true);

	//navigation
	output_pagination($start, $count);
	echo('<p>---</p>');

	//output actions
	while($row = $actions->fetch_object()) {
		echo("<p><span class='date'>$row->time</span> -- ".htmlspecialchars($row->message)."</p>");
	}

	echo('<p>---</p>');
	output_pagination($start, $count);
	output_footer();
}

catch(Exception $e){
	$error = $e->getMessage();
	output_header('Mod Action Log', true);
	echo "Error: $error";
	output_footer();
}

?>
