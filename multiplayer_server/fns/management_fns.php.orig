<<<<<<< HEAD:server/fns/management_fns.php
<?php


// starts a server if it is not running
function test_server($script, $address, $port, $key, $server_id) {
	output('');
	output("begining test for server at $address:$port ($script)");

	$result = connect_to_server($address, $port, $key);
	//if it could connect
	if($result){
		echo ("good, the server is working \n");
	}
	else{
		echo ("bad (or no) response from the server \n");

		$pid = read_pid($port);
		shut_down_server($pid, $address, $port);

		start_server($script, $port, $server_id);
	}

	output('');
}



//starts the dots server
function start_server($script, $port, $server_id) {
	output("start_server: $script, $port");

	$log = '/home/jiggmin/pr2/log/'.$port.'-'.date("Fj-Y-g:ia").'.log';
	$command = "nohup php $script $server_id > $log & echo $!";
	output("executing command: $command");
	$pid = exec($command);

	write_pid($pid, $port);
	return($pid);
}



//tests if you can connect to a server
function connect_to_server($address, $port, $key){
	echo ("connect_to_server $address $port $key\n");
	$result = talk_to_server( $address, $port, $key, 'check_status`', true );
	return $result;
}



//graceful shutdown
function shut_down_server($pid, $address, $port){
	$result = false;
	$result = call_socket_function($port, 'shut_down`', $address, true);
	if(!$result){
		kill_pid($pid);
	}
}


//write pid to a file
function write_pid($pid, $port) {
	$pid_file = get_pid_file($port);
	echo ("save pid to $pid_file: $pid \n");
	$handle = fopen($pid_file, 'w');
	if( $handle ) {
		fwrite($handle, $pid);
		fclose($handle);
	}
}


//read pid from file
function read_pid($port) {
	$pid_file = get_pid_file($port);
	$pid = 0;
	echo ("reading pid from $pid_file... \n");
	$handle = fopen($pid_file, 'r');
	if($handle !== false) {
		$pid = fread($handle, 999);
		fclose($handle);
		echo ("pid is: $pid \n");
	}
	else {
		echo ("pid file does not exist \n");
	}
	return($pid);
}


//kills a pid
function kill_pid($pid){
	if($pid != NULL && $pid != 0 && $pid != ''){
		system("kill ".$pid, $k);
		$pid = NULL;
		if(!$k){
			return true;
		}else{
			return false;
		}
	}else{
		output('there is no pid to kill');
		return true;
	}
}


function get_pid_file($port) {
	$pid_file = '/home/jiggmin/pr2/pid/'.$port.'.txt';
	return($pid_file);
}


function talk_to_server_id( $db, $server_id, $message, $receive ) {
	$server = $db->grab_row( 'server_select', array($server_id) );
	//$reply = talk_to_server( $server->address, $server->port, $server->salt, $message, $receive );
	$reply = talk_to_server( 'localhost', $server->port, $server->salt, $message, $receive );
	return( $reply );
}


function talk_to_server( $address, $port, $key, $message, $receive ) {
  global $PROCESS_PASS;
	$end = chr(0x04);
	$send_num = 1;
	$data = $PROCESS_PASS;
	$intro_function = 'become_process';
	$str_to_hash = $key . $send_num . '`' . $intro_function . '`' . $data;
	$local_hash = md5($str_to_hash);
	$sub_hash = substr($local_hash, 0, 3);

	$message1 = $sub_hash .'`'. $send_num .'`'. $intro_function .'`'. $data . $end;
	$message2 = $message . $end;
	$send_str = $message1 . $message2;

	$reply = true;
	$fsock = @fsockopen($address, $port, $errno, $errstr, 5);

	if($fsock){
		output('management_fns->talk_to_server write: '.$message);
		fputs($fsock, $send_str);
		stream_set_timeout($fsock, 5);
		if($receive){
			$reply = fread($fsock, 99999);
		}
		fclose($fsock);
	}
	else {
		$reply = false;
		output("management_fns->talk_to_server error: could not connect to $address $port $key");
	}

	if($receive && $reply === ''){
		$reply = false;
	}
	else{
		$reply = substr($reply, 0, strlen($reply)-1);
	}

	output('management_fns->talk_to_server read: '.$reply);
	return($reply);
}


//--- connects to the farm server and calls a function -------------------------------------
function call_socket_function($port, $server_function, $server='jiggmin2.com', $recieve=false) {
  global $COMM_PASS, $PROCESS_PASS;
	$end = chr(0x04);
	$send_num = 1;
	$data = $PROCESS_PASS;
	$intro_function = 'become_process';
	$str_to_hash = $COMM_PASS . $send_num . '`' . $intro_function . '`' . $data;
	$local_hash = md5($str_to_hash);
	$sub_hash = substr($local_hash, 0, 3);

	$message1 = $sub_hash .'`'. $send_num .'`'. $intro_function .'`'. $data . $end;
	$message2 = $server_function . $end;
	$send_str = $message1 . $message2;

	$reply = true;
	$fsock = @fsockopen($server, $port, $errno, $errstr, 5);

	if($fsock){
		output('management_fns->call_socket_function write: '.$send_str);
		fputs($fsock, $send_str);
		stream_set_timeout($fsock, 5);
		if($recieve){
			$reply = fread($fsock, 99999);
		}
		fclose($fsock);
	}
	else {
		$reply = false;
	}

	output('management_fns->call_socket_function read: '.$reply);

	if($recieve && $reply == ''){
		$reply = false;
	}
	else{
		$reply = substr($reply, 0, strlen($reply)-1);
	}

	return($reply);
}


function output($str) {
	echo $str . "\n";
}

?>
=======
<?php

require_once(__DIR__ . '/data_fns.php');


// starts a server if it is not running
function test_server($script, $address, $port, $key, $server_id) {
	output('');
	output("begining test for server at $address:$port ($script)");

	$result = connect_to_server($address, $port, $key);
	//if it could connect
	if($result){
		echo ("good, the server is working \n");
	}
	else{
		echo ("bad (or no) response from the server \n");

		$pid = read_pid($port);
		shut_down_server($pid, $address, $port);

		start_server($script, $port, $server_id);
	}

	output('');
}



//starts the dots server
function start_server($script, $port, $server_id) {
	output("start_server: $script, $port");

	$log = '/home/jiggmin/pr2/log/'.$port.'-'.date("Fj-Y-g:ia").'.log';
	$command = "nohup php $script $server_id > $log & echo $!";
	output("executing command: $command");
	$pid = exec($command);

	write_pid($pid, $port);
	return($pid);
}



//tests if you can connect to a server
function connect_to_server($address, $port, $key){
	echo ("connect_to_server $address $port $key\n");
	$result = talk_to_server( $address, $port, $key, 'check_status`', true );
	return $result;
}



//graceful shutdown
function shut_down_server($pid, $address, $port){
	$result = false;
	$result = talk_to_server($port, 'shut_down`', $address, true);
	if(!$result){
		kill_pid($pid);
	}
}


//write pid to a file
function write_pid($pid, $port) {
	$pid_file = get_pid_file($port);
	echo ("save pid to $pid_file: $pid \n");
	$handle = fopen($pid_file, 'w');
	if( $handle ) {
		fwrite($handle, $pid);
		fclose($handle);
	}
}


//read pid from file
function read_pid($port) {
	$pid_file = get_pid_file($port);
	$pid = 0;
	echo ("reading pid from $pid_file... \n");
	$handle = fopen($pid_file, 'r');
	if($handle !== false) {
		$pid = fread($handle, 999);
		fclose($handle);
		echo ("pid is: $pid \n");
	}
	else {
		echo ("pid file does not exist \n");
	}
	return($pid);
}


//kills a pid
function kill_pid($pid){
	if($pid != NULL && $pid != 0 && $pid != ''){
		system("kill ".$pid, $k);
		$pid = NULL;
		if(!$k){
			return true;
		}else{
			return false;
		}
	}else{
		output('there is no pid to kill');
		return true;
	}
}


function get_pid_file($port) {
	$pid_file = '/home/jiggmin/pr2/pid/'.$port.'.txt';
	return($pid_file);
}


function talk_to_server_id( $db, $server_id, $message, $receive ) {
	$server = $db->grab_row( 'server_select', array($server_id) );
	$reply = talk_to_server( 'localhost', $server->port, $server->salt, $message, $receive );
	return( $reply );
}


function talk_to_server( $address, $port, $key, $message, $receive ) {
  global $PROCESS_PASS;
	$end = chr(0x04);
	$send_num = 1;
	$data = $PROCESS_PASS;
	$intro_function = 'become_process';
	$str_to_hash = $key . $send_num . '`' . $intro_function . '`' . $data;
	$local_hash = md5($str_to_hash);
	$sub_hash = substr($local_hash, 0, 3);

	$message1 = $sub_hash .'`'. $send_num .'`'. $intro_function .'`'. $data . $end;
	$message2 = $message . $end;
	$send_str = $message1 . $message2;

	$reply = true;
	$fsock = @fsockopen($address, $port, $errno, $errstr, 5);

	if($fsock){
		output('management_fns->talk_to_server write: '.$message);
		fputs($fsock, $send_str);
		stream_set_timeout($fsock, 5);
		if($receive){
			$reply = fread($fsock, 99999);
		}
		fclose($fsock);
	}
	else {
		$reply = false;
		output("management_fns->talk_to_server error: could not connect to $address $port $key");
	}

	if($receive && $reply === ''){
		$reply = false;
	}
	else{
		$reply = substr($reply, 0, strlen($reply)-1);
	}

	output('management_fns->talk_to_server read: '.$reply);
	return($reply);
}


function output($str) {
	echo $str . "\n";
}

?>
>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/fns/management_fns.php
