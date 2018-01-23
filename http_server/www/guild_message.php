<?php

require_once('../fns/all_fns.php');
require_once('../fns/pr2_fns.php');

$message = find('message');
$ip = get_ip();

try {
	
	//--- rate limit
	rate_limit( 'guildMessage-'.$ip, 60*5, 1, 'Only one guild message can be sent every five minutes.' );
	
	//--- connect
	$db = new DB();
	
	//--- confirm login
	$user_id = token_login($db, false);
	
	//--- confirm that they are in a guild
	$guild_id = $db->grab( 'guild', 'user_select', array($user_id) );
	if( $guild_id <= 0 ) {
		throw new Exception( 'You are not in a guild.' );
	}
	
	//--- send message to each member
	$members = $db->to_array( $db->call( 'guild_select_members', array($guild_id) ) );
	foreach( $members as $member ) {
		$db->call( 'message_insert', array( $member->user_id, $user_id, $message, $ip ) );
	}
	
	echo 'message=Your message was sent successfully!';		
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>
