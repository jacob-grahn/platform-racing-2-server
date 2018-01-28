<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$action = find('action', 'lookup');
$message = find('message', '');

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
	$message = 'Error: ' . $e->getMessage();
	output_form($db, $message);
}


function output_form($db, $message) {
	
	output_header('Set Campaign', true, true);
	
	// if there's a message, display it to the user
	if($message != '') {
		echo "<p><b>$message</b></p>";
	}
	
	// select the custom campaign
	$campaign = $db->to_array($db->call('campaign_select'));
	$campaign = $campaign[5]; // 0 = Original, 1 = Speed, 2 = Luna, 3 = Timeline, 4 = Legendary, 5 = Custom
	
	echo '<form name="input" action="set_campaign.php" method="get">';
	
	echo "Set Custom Campaign <br>---<br>";

	echo 'Level 1: <input type="text" size="" name="l1" value="'.htmlspecialchars($campaign->levelID0).'"><br>';
	echo 'Level 2: <input type="text" size="" name="l2" value="'.htmlspecialchars($campaign->levelID1).'"><br>';
	echo 'Level 3: <input type="text" size="" name="l3" value="'.htmlspecialchars($campaign->levelID2).'"><br>';
	echo 'Level 4: <input type="text" size="" name="l4" value="'.htmlspecialchars($campaign->levelID3).'"><br>';
	echo 'Level 5: <input type="text" size="" name="l5" value="'.htmlspecialchars($campaign->levelID4).'"><br>';
	echo 'Level 6: <input type="text" size="" name="l6" value="'.htmlspecialchars($campaign->levelID5).'"><br>';
	echo 'Level 7: <input type="text" size="" name="l7" value="'.htmlspecialchars($campaign->levelID6).'"><br>';
	echo 'Level 8: <input type="text" size="" name="l8" value="'.htmlspecialchars($campaign->levelID7).'"><br>';
	echo 'Level 9: <input type="text" size="" name="l9" value="'.htmlspecialchars($campaign->levelID8).'"><br>';
	
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

	//define which campaign we're updating
	$campaign_id = 6;
	
	foreach (range(1,9) as $id) {
	
		// get individual level IDs
		${'l' . $id} = (int) find('l' . $id);
		// clean up variable
		$l = ${'l' . $id};
		
		try {
		
			$level = $db->grab_row('level_select', array($l));
			
			if (!level) {
				throw new Exception("Level $id $l does not exist.");
			}
			
		}
		
		catch (Exception $e) {
			$message = "Error: " .  $e->getMessage();
			output_form($db, $message);
		}
	}

	$db->call(
		'campaign_update',
		array(
		$campaign_id,
		$l1,
		$l2,
		$l3,
		$l4,
		$l5,
		$l6,
		$l7,
		$l8,
		$l9
		)
	);
	
	//admin log
	$admin_name = $admin->name;
	$admin_id = $admin->user_id;
	$ip = get_ip();
	
	$db->call('admin_action_insert', array($admin_id, "$admin_name set a new custom campaign from $ip", $admin_id, $ip));
	
	//redirect back to the script
	$message = "Great success! The new campaign has been set. It will take effect at the top of the next hour.";
	header("Location: set_campaign.php?message=" . urlencode($message));
	die();

}

?>