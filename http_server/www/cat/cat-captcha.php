<?php

header("Content-type: text/plain");

require_once( '../../fns/all_fns.php' );

$ip = get_ip();

try {

	//--- rate limit
	// rate_limit('cat-init-'.$ip, 60*5, 10)

  $key = 'cat-' . $ip;

	// if( apcu_exists($key) ) {
	// 	throw new Exception('Your account already has a captcha pending.');
	// }

	$cat = '/home/jiggmin/pr2hub/cats/PetImages/Cat/'.rand(0, 12499).'.jpg';
	$dog = '/home/jiggmin/pr2hub/cats/PetImages/Dog/'.rand(0, 12499).'.jpg';

	$val = new stdClass();
	if( rand(0,1) == 0 ) {
		$val->imgs = array($cat, $dog);
		$val->answer = 0;
	}
	else {
		$val->imgs = array($dog, $cat);
		$val->answer = 1;
	}
	$str_val = json_encode($val);

	apcu_store($key, $str_val, 120);

	$reply = new stdClass();
	$reply->success = true;
	echo( json_encode($reply) );
}

catch(Exception $e) {
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo( json_encode($reply) );
}


?>
