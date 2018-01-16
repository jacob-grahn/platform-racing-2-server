<?php

require_once('../fns/all_fns.php');

try {
	
	if(isset($_COOKIE['token'])) {
	    
	    //--- connect to the db
	    $db = new DB();
	
	    //--- delete token from db
	    $db->call('token_delete_2', array($_COOKIE['token']), 'Could not delete token from db.' );
	
	    //--- delete cookie
	    setcookie ("token", "", time() - 3600);
	}
	
	echo 'success=true';
}

catch(Exception $e) {
	echo 'error='.$e->getMessage();
}

?>