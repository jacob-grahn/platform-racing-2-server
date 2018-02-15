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
	$row = $db->grab_row('guild_transfer_select', array($code), 'No pending change was found.');
	$guild_id = $row->guild_id;
	$old_id = $row->old_id;
	$new_id = $row->new_id;
	$change_id = $row->change_id;
	
	$db->call('guild_transfer_complete', array($change_id, $ip), 'Could not confirm the transfer.');
	$db->call('user_transfer_guild', array($user_id, $old_email, $new_email), 'Could not transfer guild ownership.');
	//$db->call( 'guild_transfer_delete', array($code), 'Could not clean up.' );
	
	// tell the world
	echo "Great success! Your email address has been changed from '$old_email' to '$new_email'.";
	output_footer();
}


catch(Exception $e){
	$message = $e->getMessage();
	echo "Error: $message";
	output_footer();
}

?>
