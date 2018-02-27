<?php

header("Content-type: text/plain");
require_once('../fns/all_fns.php');

$email = $_POST['email'];

try {

	// sanity check: was anything submitted?
	if(strlen($email) <= 0){
		throw new Exception('You must enter an email.');
	}

	// connect
	$db = new DB();
	
	// check their login
	$user_id = token_login($db,false);
	$user = $db->grab_row('user_select', array($user_id));
	
	// sanity check: are they a guest?
	if($user->power < 1){
		throw new Exception("Guests don't even really have accounts...");
	}
	
	// safety first
	$safe_email = $db->escape($email);
	$safe_user_id = $db->escape($user_id);
	
	// do it
	$query = "update users
				set email = '$safe_email'
				where user_id = '$safe_user_id'
				and email = ''
				limit 1";
	$result = $db->query($query);
	
	// if you couldn't do it, tell them
	if(!$result){
		throw new Exception('Could not update your email. Sorries.');
	}
	
	// tell the world
	echo 'message=Your email address has been set!';
	
}
catch (Exception $e) {
	$error = $e->getMessage();
	echo "error=$error";
}




?>
