<?php

require_once('../fns/all_fns.php');
require_once('../fns/to_hash.php');


$name = $_POST['name'];
$email = $_POST['email'];

$safe_name = addslashes($name);
$safe_email = addslashes($email);


try{

	if(!valid_email($email)){
		throw new Exception("'" . htmlspecialchars($email) . "' is not a valid email address.");
	}
	if(strtolower($name) == 'jiggmin') {
		throw new Exception('The password to Jiggmin\'s luggage is 12345.');
	}

	$db = new DB();


	//--- get thier user id -----------------------------------------------------------------
	$result = $db->query("select user_id
									from users
									where email = '$safe_email'
									and name = '$safe_name'
									limit 0, 1");

	if(!$result){
		throw new Exception('Could not get your id from the database.');
	}
	if($result->num_rows <= 0){
		throw new Exception('No account was found with the username "' . htmlspecialchars($name) . '" and the email address "' . htmlspecialchars($email) . '".');
	}
	if($result->num_rows > 1){
		throw new Exception('More than one result was returned. Something has gone horribly wrong, probably the world is about to explode.');
	}



	//--- give them a new pass -----------------------------------------------------------------------
	$row = $result->fetch_object();
	$user_id = $row->user_id;

	$pass = random_str(12);
	$pass_hash = to_hash($pass);
	$safe_pass_hash = addslashes($pass_hash);

	$result = $db->query("update users
									set temp_pass_hash = '$safe_pass_hash'
									where user_id = '$user_id'");
	if(!$result){
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
				."If you didn't request this email, then just ignore it. Your old password will still work as long as you don't log in with this one.";

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




	//--- output what is hopefully success!
	echo 'message=Great success! You should receive an email with your new password shortly.';


}
catch(Exception $e){
	echo 'error='.$e->getMessage();
	exit();
}


?>
