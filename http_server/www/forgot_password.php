<?php

header("Content-type: text/plain");

require_once('../fns/all_fns.php');
require_once('../fns/to_hash.php');

$name = $_POST['name'];
$email = $_POST['email'];
$safe_name = addslashes($name);
$safe_email = addslashes($email);
$ip = get_ip();

try {
	
	// check for post
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception("Invalid request method.");
	}
	
	// check referrer
	$ref = check_ref();
	if ($ref !== true) {
		throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only request a new password from an approved site such as pr2hub.com.");
	}
	
	// rate limiting
	rate_limit('forgot-password-attempt-'.$ip, 60, 1, 'Please wait at least one minute before submitting another request to recover your account.');

	// sanity check: is it a valid email address?
	if(!valid_email($email)){
		$safe_disp_email = htmlspecialchars($email);
		throw new Exception("\"$safe_disp_email\" is not a valid email address.");
	}
	
	// easter egg: Jiggmin's luggage
	if(strtolower($name) == 'jiggmin') {
		throw new Exception("The password to Jiggmin's luggage is 12345.");
	}

	// connect to the db
	$db = new DB();

	// get their user id
	$result = $db->query("SELECT user_id
									FROM users
									WHERE email = '$safe_email'
									AND name = '$safe_name'
									LIMIT 0, 1");

	if(!$result){
		throw new Exception('Could not get your id from the database.');
	}
	if($result->num_rows <= 0) {
		$safe_disp_name = htmlspecialchars($name);
		$safe_disp_email = htmlspecialchars($email);
		throw new Exception("No account was found with the username \"$safe_disp_name\" and the email address \"$safe_disp_email\".");
	}
	if($result->num_rows > 1) {
		throw new Exception('More than one result was returned. Something has gone horribly wrong and the world is about to explode.');
	}

	// get the user id
	$row = $result->fetch_object();
	$user_id = $row->user_id;
	
	// more rate limiting
	rate_limit('forgot-password-'.$user_id, 900, 1, 'You may only request a new password once every 15 minutes.');

	// give them a new pass
	$pass = random_str(12);
	$pass_hash = to_hash($pass);
	$safe_pass_hash = addslashes($pass_hash);

	$result = $db->query("UPDATE users
									SET temp_pass_hash = '$safe_pass_hash'
									WHERE user_id = '$user_id'");
	if(!$result) {
		throw new Exception('Could not update your password.');
	}



	//--- email them their new pass ---------------------------------------------------------------------
	include('Mail.php');

	$recipients = $email;

	$headers = array();
	$headers['From']    = 'Fred the Giant Cactus <contact@jiggmin.com>';
	$headers['To']      = $email;
	$headers['Subject'] = 'A password and chocolates from PR2';

	$body = "Hi $name,\n\n"
				."It seems you forgot your password. Here's a new one: $pass\n\n"
				."If you didn't request this email, then just ignore it. Your old password will still work as long as you don't log in with this one.\n\n"
				."All the best,\n"
				."Fred";

	// Define SMTP Parameters
	$params['host'] = $EMAIL_HOST;
	$params['port'] = '465';
	$params['auth'] = 'PLAIN';
	$params['username'] = $EMAIL_USER;
	$params['password'] = $EMAIL_PASS;

	// Create the mail object using the Mail::factory method
	$mail_object =& Mail::factory('smtp', $params);

	// Send the message
	$mail_object->send($recipients, $headers, $body);

	// tell the world
	echo 'message=Great success! You should receive an email with your new password shortly.';
	
	// goodbye
	die();
	
}
catch(Exception $e){
	$error = $e->getMessage();
	echo "error=$error";
	die();
}


?>
