<?php

require_once( '../fns/all_fns.php' );
require_once( '../fns/output_fns.php' );

try {
	
	//--- import data
	$code = find( 'code' );
	$ip = get_ip();
	
	
	//--- sanity check
	if( !isset($code) ) {
		throw new Exception( 'No code found.' );
	}
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- 
	$row = $db->grab_row( 'changing_email_select', array($code), 'No pending change was found.' );
	$user_id = $row->user_id;
	$old_email = $row->old_email;
	$new_email = $row->new_email;
	$change_id = $row->change_id;
	
	$db->call( 'changing_email_complete', array($change_id, $ip), 'Could not confirm the change.' );
	$db->call( 'user_update_email', array($user_id, $old_email, $new_email), 'Could not update your email.' );
	//$db->call( 'changing_email_delete', array($code), 'Could not clean up.' );
	
	
	//---
	output_header( 'Confirm Email Change' );
	echo "Great success! Your email address has been changed from '$old_email' to '$new_email'.";
	output_footer();
}


catch(Exception $e){
	output_header( 'Confirm Email Change' );
	echo $e->getMessage();
	output_footer();
}

?>
