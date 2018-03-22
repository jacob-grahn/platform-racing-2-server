<?php

require_once __DIR__ . '/../../env.php';

function user_connect()
{
    global $DB_PASS, $DB_ADDRESS, $DB_USER, $DB_NAME, $DB_PORT;
    $mysqli = new mysqli($DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    if ($mysqli->connect_error) {
        throw new Exception('Connect Error ('.$mysqli->connect_errno.') '.$mysqli->connect_error);
    }
    return $mysqli;
}


//--- lookup user_id with name ---------------------------------------------------------
function name_to_id($connection, $name)
{
    $safe_name = addslashes($name);
    $result = $connection->query(
        "select user_id
									from users
									where name = '$safe_name'
									LIMIT 0,1"
    );
    if (!$result) {
        throw new Exception('Could not look up user "'.$name.'".');
    }
    if ($result->num_rows <= 0) {
        throw new Exception('No user with that the name "'.$name.'" was found.');
    }
    $row = $result->fetch_object();
    $user_id = $row->user_id;

    return $user_id;
}
