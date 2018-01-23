<?php

function api_test_ip() {
	$ip = get_ip();	
	$allowed_ips = array('104.237.157.71', '50.116.47.81');
	if(array_search($ip, $allowed_ips) === false) {
		throw new Exception('bad ip');
	}
}

?>
