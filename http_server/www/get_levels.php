<?php

require_once('../fns/all_fns.php');

$count = find('count', 100);

try {
	
	$db = new DB();
	$user_id = token_login($db);
	
	$result = $db->call('levels_select_by_owner', array($user_id));
	$str = format_level_list($result, $count);
	
	echo $str;
}
catch(Exception $e){
	echo 'error='.$e->getMessage();
}

?>