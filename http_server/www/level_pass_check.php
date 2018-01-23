<?php

require_once('../fns/all_fns.php');
require_once('../fns/Encryptor.php');

$level_id = find('courseID');
$hash = find('hash');

try {

	//sanity
	if(!$level_id || !$hash) {
		throw new Exception('Invalid input. ' . join(', ', $_GET));
	}

	//connect
	$db = new DB();

	//check thier login
	$user_id = token_login($db, false);

	//check the pass
	$hash2 = sha1($hash . $LEVEL_PASS_SALT);
	$match = $db->grab('isMatch', 'level_check_pass', array($level_id, $hash2));
	if(!$match) {
		sleep(1);
	}

	//return info
	$result = new stdClass();
	$result->access = $match;
	$result->level_id = $level_id;
	$result->user_id = $user_id;
	$str_result = json_encode($result);

	//set up encryptor
	$encryptor = new Encryptor();
	$encryptor->set_key($LEVEL_PASS_KEY);
	$enc_result = $encryptor->encrypt($str_result, $LEVEL_PASS_IV);

	echo 'result=' . urlencode($enc_result);
}

catch(Exception $e){
	echo 'error=' . urlencode($e->getMessage());
}
