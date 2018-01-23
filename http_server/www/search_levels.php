<?php

require_once('../fns/all_fns.php');

$mode = find('mode', 'user');
$search_str = find('search_str', '');
$order = find('order', '');
$dir = find('dir', 'desc');
$page = find('page', '1');
$ip = get_ip();

$page = min(25, $page);
$key = "search-$mode-$search_str-$order-$dir-$page";
$cache_expire = 60 * 10; //10 minutes

try {

	$page_str = apcu_fetch($key);

	while ($page_str === 'WAIT') {
		sleep(1);
		$page_str = apcu_fetch($key);
	}

	if($page_str === false) {
		rate_limit("$ip-search", 10, 5);
		apcu_add($key, 'WAIT', 5); // will not overwrite existing
		$page_str = search_levels($mode, $search_str, $order, $dir, $page);
		apcu_store($key, $page_str, $cache_expire); // will overwrite existing
	}

	echo $page_str;

}

catch(Exception $e){
	echo 'error='.$e->getMessage();
}

?>
