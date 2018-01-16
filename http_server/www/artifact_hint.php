<?php

require_once('../fns/all_fns.php');
require_once('../fns/output_fns.php');

output_header("Artifact Hunter");

try {
	$db = new DB();
	$artifact = $db->grab_row( 'artifact_location_select' );
}
catch(Exception $e){
	echo 'Error: '.$e->getMessage();
}

output_footer();

?>