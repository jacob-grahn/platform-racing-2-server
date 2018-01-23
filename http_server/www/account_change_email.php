<?php

header("Content-type: text/plain");

require_once( '../fns/all_fns.php' );
require_once( '../fns/Encryptor.php' );
require_once( '../fns/email_fns.php' );

$encrypted_data = find( 'data' );

$ip = get_ip();

try{

	//--- sanity check
	if( !isset($encrypted_data) ) {
		throw new Exception('No data was recieved.');
	}

	//--- decrypt
	$encryptor = new Encryptor();
	$encryptor->set_key($CHANGE_EMAIL_KEY);
	$str_data = $encryptor->decrypt($encrypted_data, $CHANGE_EMAIL_IV);
	$data = json_decode($str_data);
	$new_email = $data->email;
	$pass = $data->pass;

	//--- connect
	$db = new DB();

	//--- check thier login
	$user_id = token_login( $db, false );
	$user = $db->grab_row( 'user_select', array($user_id) );
	$old_email = $user->email;

	//--- check pass
	pass_login($db, $user->name, $pass);

	//--- more sanity checks
	if( $user->power < 1 ) {
		throw new Exception( 'Guests don\'t even really have accounts...' );
	}
	if( !valid_email($new_email) ){
		throw new Exception("'$new_email' is not a valid email address.");
	}
	rate_limit( 'change-email-'.$user_id, 86400, 2, 'Your email can be changed a maximum of two times per day.' );
	rate_limit( 'change-email-'.$ip, 86400, 2, 'Your email can be changed a maximum of two times per day.' );

	//--- update their email if they don't have one
	if( $user->email == '' || !isset($user->email) ) {
		$db->call( 'user_update_email', array($user_id, $old_email, $new_email) );
	}

	//--- begin an email change confirmation if they do already have an email address
	$code = random_str(24);
	$db->call( 'changing_email_insert', array($user_id, $old_email, $new_email, $code, $ip) );

	//--- send a confirmation email
	$from = 'Fred the Giant Cactus <contact@jiggmin.com>';
	$to = $old_email;
	$subject = 'PR2 Email Change Confirmation';
	$body = "Howdy $safe_name,\n\nWe received a request to change the email on your account from $old_email to $new_email. If you requested this change, please click the link below to change the email address on your Platform Racing 2 account.\n\nhttp://pr2hub.com/account_confirm_email_change.php?code=$code\n\nIf you didn\'t request this change, you may need to change your password.\n\nAll the best,\nFred";
	send_email( $from, $to, $subject, $body );

	//--- tell it to the world
	$ret = new stdClass();
	$ret->message = 'A confirmation email has been sent to your old email address.';
	echo json_encode( $ret );
}


catch(Exception $e){
	$ret = new stdClass();
	$ret->error = $e->getMessage();
	echo json_encode( $ret );
}

?>
