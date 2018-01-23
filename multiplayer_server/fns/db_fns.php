<?php

require_once(__DIR__ . '/../../env.php');

function user_connect(){
	global $DB_PASS, $DB_ADDRESS, $DB_USER, $DB_NAME, $DB_PORT;
	$mysqli = new mysqli($DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
	if ($mysqli->connect_error) {
		throw new Exception('Connect Error ('.$mysqli->connect_errno.') '.$mysqli->connect_error);
	}
	return $mysqli;
}



//--- check if a name is taken -------------------------------------------------------------
function check_for_name($connection, $name){
	$safe_name = addslashes($name);
	$result = $connection->query("select name from users
									where name = '$safe_name'");
	if(!$result){
		throw new Exception('Could not check if '.$name.' was in db.');
	}
	if($result->num_rows >= 1){
		return true;
	}else{
		return false;
	}
}


//--- gets a row from a database --------------------------------------------------------
function get_row($connection, $table, $id_name, $id){
	$safe_table = addslashes($table);
	$safe_id_name = addslashes($id_name);
	$safe_id = addslashes($id);
	$result = $connection->query("select * from $table
									where $id_name = '$id'");

	if(!$result){
		throw new Exception("Could not get row $table, $id_name = $id.");
	}

	return $result->fetch_object();
}



//--- ban a member ----------------------------------------------------------------------
function ban_member($connection, $user_id, $duration){
	$safe_user_id = addslashes($safe_user_id);
	$safe_ban_time = addslashes(time()+duration);

	$result = $connection->query("update users
									set ban_time = '$safe_ban_time'
									where user_id = '$safe_user_id'");
	if(!$result){
		throw new Exception("Could not ban user $user_id");
	}
}



//--- lookup user_id with name ---------------------------------------------------------
function name_to_id($connection, $name) {
	$safe_name = addslashes($name);
	$result = $connection->query("select user_id
									from users
									where name = '$safe_name'
									LIMIT 0,1");
	if(!$result){
		throw new Exception('Could not look up user "'.$name.'".');
	}
	if($result->num_rows <= 0){
		throw new Exception('No user with that the name "'.$name.'" was found.');
	}
	$row = $result->fetch_object();
	$user_id = $row->user_id;

	return $user_id;
}



//--- lookup name with user_id ----------------------------------------------------------
function id_to_name($connection, $user_id){
	$safe_user_id = addslashes($user_id);
	$result = $connection->query("select name
									from users
									where user_id = '$safe_user_id'
									LIMIT 0,1");
	if(!$result){
		throw new Exception('Could not look up user id.');
	}
	if($result->num_rows <= 0){
		throw new Exception("No user with that that id $user_id was found.");
	}

	$row = $result->fetch_object();
	$name = $row->name;

	return $name;
}



//--- insert an info pair ---------------------------------------------------------------
function insert_db_pair($connection, $db, $var1, $var2, $val1, $val2){
	//check if this will be redundant
	$safe_db = addslashes($db);
	$safe_var1 = addslashes($var1);
	$safe_var2 = addslashes($var2);
	$safe_val1 = addslashes($val1);
	$safe_val2 = addslashes($val2);
	$result = $connection->query("select * from $safe_db
									where $safe_var1 = '$safe_val1'
									and $safe_var2 = '$safe_val2'");
	if(!$result){
		throw new Exception('Could not check the database.');
	}
	if($result->num_rows > 0){
		throw new Exception('That entry is already in the db.');
	}

	//add the magical one sided friendship to the db
	$result = $connection->query("insert into $safe_db
									set $safe_var1 = '$safe_val1',
										$safe_var2 = '$safe_val2'");
	if(!$result){
		throw new Exception('Could not add db pair: $db, $var1=$val1, $var2=$val2');
	}
}



//--- checks if an account is banned -----------------------------------------------
function query_ban_record($connection, $where){
	$time = time();
	$result = $connection->query("select *
									from bans
									where $where
									and lifted != 1
									and expire_time > '$time'");
	if(!$result){
		throw new Exception('Could not check your account status');
	}
	return($result);
}



?>
