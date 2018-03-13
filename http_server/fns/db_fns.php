<?php

require_once __DIR__ . '/data_fns.php';

function pr2_connect()
{
    global $DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;

    // rate limit anything that uses the db
    $ip = get_ip();
    if (isset($ip)) {
        rate_limit($ip, 30, 15, 'Max 15 connections in 30 seconds');
    }

    // connect to the db
    $mysqli = new mysqli($DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    if ($mysqli->connect_error) {
        throw new Exception('Could not connect to pr2\'s database. ');
    }
    return $mysqli;
}

function fah_connect()
{
    global $DB_ADDRESS, $DB_FAH_USER, $DB_FAH_PASS, $DB_FAH_NAME, $DB_PORT;
    $mysqli = new mysqli($DB_ADDRESS, $DB_FAH_USER, $DB_FAH_PASS, $DB_FAH_NAME, $DB_PORT);
    if ($mysqli->connect_error) {
        throw new Exception('Could not connect to fah\'s database. ');
    }
    return $mysqli;
}

function s3_connect()
{
    global $S3_SECRET, $S3_PASS;
    $s3 = new S3($S3_SECRET, $S3_PASS);
    return($s3);
}
