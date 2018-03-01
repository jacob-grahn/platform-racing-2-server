<?php

require_once('../fns/all_fns.php');
require_once('../fns/to_hash.php');

$name = $_POST['name'];
$password = $_POST['password'];
$email = $_POST['email'];
$time = time();
$ip = get_ip();

try {
	
	// POST check
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception("Invalid request method.");
	}
	
	// check referrer
	$ref = check_ref();
	if ($ref !== true) {
		throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only register a new account from an approved site such as pr2hub.com.");
	}
	
	// rate limiting (check if the IP address is spamming)
	rate_limit('register-account-attempt-'.$ip, 10, 2, 'Please wait at least 10 seconds before trying to create another account.');

	// error check
	if(empty($name) || !is_string($password) || $password == ''){
		throw new Exception('You must enter a name and a password to register an account.');
	}
	if(strlen($name) < 2){
		throw new Exception('Your name must be at least 2 characters long.');
	}
	if(strlen($name) > 20){
		throw new Exception('Your name can not be more than 20 characters long.');
	}
	if(strpos($name, '`') !== false){
		throw new Exception('The ` character is not allowed.');
	}
	if($email != '' && !valid_email($email)){
		throw new Exception("'$email' is not a valid email address.");
	}
	if(is_obsene($name)){
		throw new Exception('Keep your username clean, pretty please!');
	}
	$test_name = preg_replace("/[^a-zA-Z0-9-.:;=?~! ]/", "", $name);
	if($test_name != $name){
		throw new Exception('Your name is invalid. You may only use alphanumeric characters, spaces and !-.:;=?~.');
	}

	// connect to the db
	$db = new DB();

	// check if banned
	check_if_banned($db, -1, $ip);

	// check if this name has been taken already
	$result = $db->call('users_check_name', array($name));
	if($result->num_rows >= 1) {
		throw new Exception('Sorry, that name has already been registered.');
	}
	
	// more rate limiting (check if too many accounts have been made from this ip today)
	rate_limit('register-account-'.$ip, 86400, 5, 'You may create a maximum of five accounts from the same IP address per day.');

	// everything looks good, so register the user
	$pass_hash = to_hash($password);
	$db->call('user_insert', array($name, $pass_hash, $ip, $time, $email));

	$ret = new stdClass();
	$ret->result = 'success';
	echo json_encode( $ret );
}

catch(Exception $e){
	$ret = new stdClass();
	$ret->error = $e->getMessage();
	echo json_encode( $ret );
}


?>
