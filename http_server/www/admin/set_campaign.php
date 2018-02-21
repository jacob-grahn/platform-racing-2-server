<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$action = find('action', 'lookup');
$message = find('message', '');
$campaign_id = 6; // 1 = Original, 2 = Speed, 3 = Luna, 4 = Timeline, 5 = Legendary, 6 = Custom
$campaign = $db->to_array( $db->call('campaign_select_by_id', [$campaign_id]) ); 

try {
	
	//connect
	$db = new DB();


	//make sure you're an admin
	$admin = check_moderator($db, true, 3);
	
	
	//lookup
	if($action === 'lookup') {
		output_form($db, $message);
	}
	
	
	//update
	if($action === 'update') {
		update($db);
	}


} 

catch (Exception $e) {
	output_header('Error');
	echo 'Error: ' . $e->getMessage();
	output_footer();
}

function get_level_info( $campaign, $levelnum ) {
	$campaign_array = array();

	foreach ($campaign->$levelnum as $level) {
		${"level_id_$levelnum"} = $campaign_array[$level->level_id];
		${"prize_type_$levelnum"} = $campaign_array[$level->prize_type];
		${"prize_id_$levelnum"} = $campaign_array[$level->prize_id];
	}
	return $campaign_array;
}

function is_selected($prize_type, $option_value) {
	$prize_type = strtolower($prize_type);
	$option_value = strtolower($option_value);
	
	if ($option_value == $prize_type) {
		return "selected='selected'";
	}
	else {
		return '';
	}

}

function output_form($db, $message) {
	global $campaign;
	
	output_header('Set Campaign', true, true);
	
	// if there's a message, display it to the user
	if($message != '') {
		echo "<p><b>$message</b></p>";
	}
	
	$level_info = get_level_info($campaign);
	
	echo '<form name="input" action="set_campaign.php" method="get">';
	
	echo "Set Custom Campaign <br>---<br>";
	
	foreach (range(1,9) as $num) {
		
		// get level/prize information
		$level_id = $campaign[$num->level_id];
		$prize_type = $campaign[$num->prize_type];
		$prize_id = $campaign[$num->prize_id];
		
		// define prize types
		$hat = "Hat";
		$head = "Head";
		$body = "Body";
		$feet = "Feet";
		$ehat = "eHat";
		$ehead = "eHead";
		$ebody = "eBody";
		$eFeet = "eFeet";
		
		// check which type the current prize is, then select it in the dropdown
		$hat_selected = is_selected($prize_type, $hat);
		$head_selected = is_selected($prize_type, $head);
		$body_selected = is_selected($prize_type, $body);
		$feet_selected = is_selected($prize_type, $feet);
		$ehat_selected = is_selected($prize_type, $ehat);
		$ehead_selected = is_selected($prize_type, $ehead);
		$ebody_selected = is_selected($prize_type, $ebody);
		$efeet_selected = is_selected($prize_type, $efeet);
		
		$prize_html = "<select name='prize_type_$num'>
						<option value=''>Choose a type...</option>
						<option value='$hat' $hat_selected>Hat</option>
						<option value='$head' $head_selected>Head</option>
						<option value='$body' $body_selected>Body</option>
						<option value='$feet' $feet_selected>Feet</option>
						<option value='$ehat' $ehat_selected>Epic Hat</option>
						<option value='$ehead' $ehead_selected>Epic Head</option>
						<option value='$ebody' $ebody_selected>Epic Body</option>
						<option value='$efeet' $efeet_selected>Epic Feet</option>
					</select>&nbsp;<input type='text' size='' name='prize_id_$num' value='$prize_id'>";
		
		echo "Level $num: <input type='text' size='' name='level_id_$num' value='$level_id'> | Prize: $prize_html<br>";
	
	}
	
	echo '<input type="hidden" name="action" value="update">';
	
	echo '<br/>';
	echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
	echo '</form>';
	
	echo '<br>';
	echo '---';
	echo '<br>';
	echo '<pre>To set the custom campaign, gather the levels you want to set.<br>Then, find the level IDs of those levels.<br>Finally, use the level IDs to update the campaign in the form above.<br><br>NOTE: Since the data updates hourly, this process may take up to an hour to complete.</pre>';
	
	output_footer();
}

function update($db) {
	global $admin;
	global $campaign_id;
	
	foreach (range(1,9) as $id) {
	
		// get individual level details
		$level_id = (int) find("level_id_$id");
		$prize_type = find("prize_type_$id");
		$prize_id = (int) find("prize_id_$id");
		
		try {
			$level = $db->grab_row('level_select', [$level_id], "Level $id ($level_id) does not exist.");			
			$db->call('campaign_update', [$campaign_id, $id, $level_id, $prize_type, $prize_id]);	
		}
		catch (Exception $e) {
			$message = "Error: " . $e->getMessage();
			output_form($db, $message);
		}
	}

	try {		
		//admin log
		$admin_name = $admin->name;
		$admin_id = $admin->user_id;
		$ip = get_ip();
		
		$db->call('admin_action_insert', array($admin_id, "$admin_name set a new custom campaign from $ip", $admin_id, $ip));
	
	}
	catch(Exception $e) {
		$message = "Error: " . $e->getMessage();
		output_form($db, $message);
	}
	
	// did the script get here? great! redirect back to the script with a success message
	$message = "Great success! The new campaign has been set. It will take effect at the top of the next hour.";
	header("Location: set_campaign.php?message=" . urlencode($message));
	die();

}

?>
