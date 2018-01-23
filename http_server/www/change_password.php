<?php

require_once('../fns/all_fns.php');
require_once('../fns/to_hash.php');

$name = $_POST['name'];
$old_pass = $_POST['old_pass'];
$new_pass = $_POST['new_pass'];

$safe_name = addslashes($name);
$safe_old_pass = addslashes($old_pass);
$safe_new_pass = addslashes($new_pass);

try{

	if(strlen($new_pass) <= 0){
		throw new Exception('You must enter a password, silly person.');
	}

	$db = new DB();

	//check thier login
	$login = pass_login($db, $name, $old_pass);

	//ignore this if they are a guest
	$power = $login->power;
	if($power < 1){
		throw new Exception('Guests don\'t even really have passwords...');
	}

	//change thier pass
	$pass_hash = to_hash($new_pass);
	$safe_pass_hash = addslashes($pass_hash);
	$result = $db->query("update users
		set pass_hash = '$safe_pass_hash'
		where name = '$safe_name'");

	if(!$result){
		throw new Exception('Could not update your password. Sorries.');
	}

	setcookie ("token", "", time() - 3600);

	//tell it to the world
	echo 'message=Your password has been changed successfully!';
}
catch(Exception $e){
	echo 'error='.$e->getMessage();
}


?>
