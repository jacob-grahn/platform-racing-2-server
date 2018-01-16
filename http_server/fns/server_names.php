<?php

function get_server_name($port) {
	if($port == 9160){
		$server_name = 'Derron';
	}
	else if($port == 9161){
		$server_name = 'Carina';
	}
	else if($port == 9162){
		$server_name = 'Grayan';
	}
	else if($port == 9163){
		$server_name = 'Fitz';
	}
	else if($port == 9164){
		$server_name = 'Loki';
	}
	else if($port == 9165){
		$server_name = 'Promie';
	}
	else if($port == 9166){
		$server_name = 'Morgana';
	}
	else if($port == 9167){
		$server_name = 'Andres';
	}
	else if($port == 9168){
		$server_name = 'Fred';
	}
	else if($port == 9169){
		$server_name = 'Isabel';
	}
	else{
		$server_name = 'Port: '.$port;
	}
	
	return $server_name;
}
?>
