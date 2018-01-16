<?php

header("Content-type: text/plain");

require_once( '../../fns/all_fns.php' );

$ip = get_ip();
$answer = find('answer');
$reply = new stdClass();
try {
	//--- connect to the db
        $db = new DB();

	poll_servers_2($db, 'mark_human`'.$ip, false);
	$reply->success = true;
	// $reply->message = 'Congratulations on picking the cat. +500 exp';
}

catch(Exception $e) {
	echo('err');
	$reply->error = $e->getMessage();
	$reply->message = 'There was an error';
}

echo( json_encode($reply) );

?>
