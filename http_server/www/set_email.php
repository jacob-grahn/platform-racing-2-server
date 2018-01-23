<?php

require_once('../fns/all_fns.php');

$email = $_POST['email'];

try{

	if(strlen($email) <= 0){
		throw new Exception('You must enter an email.');
	}

	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	$user = $db->grab_row('user_select', array($user_id));
	
	//ignore this if they are a guest
	if($user->power < 1){
		throw new Exception('Guests don\'t even really have accounts...');
	}
	
	//change thier email
	$safe_email = $db->escape($email);
	$safe_user_id = $db->escape($user_id);
	
	$query = "update users
				set email = '$safe_email'
				where user_id = '$safe_user_id'
				and email = ''
				limit 1";
	$result = $db->query($query);
	
	if(!$result){
		throw new Exception('Could not update your email. Sorries.');
	}
	
	echo 'message=Your email has been changed successfully!';
}
catch(Exception $e){
	echo 'error='.$e->getMessage();
}




?>