<?php

require_once( '../fns/all_fns.php' );
require_once( '../fns/output_fns.php' );

try {
	
	// get the data from the link
	$code = $_GET['code'];
	$ip = get_ip();
	
	output_header('Confirm Guild Ownership Transfer');
	
	// sanity check: check for a confirmation code
	if(!isset($code)) {
		throw new Exception('No code found.');
	}
	
	// connect
	$db = new DB();
	
	// get the pending change information
	$row = $db->grab_row('guild_transfer_select_by_code', [$code], 'No pending change was found.');
	$guild_id = $row->guild_id;
	$new_owner_id = $row->new_owner_id;
	$transfer_id = $row->transfer_id;
	
	// get updated guild data
	$guild = $db->grab_row('guild_select', [$guild_id], 'Could not get updated guild information from the database.');
	$safe_guild_name = htmlspecialchars($guild->guild_name);
	$safe_new_owner = htmlspecialchars(id_to_name($db, $guild->owner_id));
	
	// do the transfer
	$db->call('guild_transfer_complete', [$transfer_id, $ip], 'Could not confirm the transfer.');
	$db->call('guild_update', [$guild_id, $guild->guild_name, $guild->emblem, $guild->note, $new_owner_id], 'Could not transfer guild ownership.');
	
	// tell the world
	echo "Great success! The new owner of $safe_guild_name is $safe_new_owner. Long live $safe_guild_name!";
	output_footer();
}


catch(Exception $e){
	$message = $e->getMessage();
	echo "Error: $message";
	output_footer();
}

?>
