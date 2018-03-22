<?php

function pdo_connect()
{
    try {
        global $DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;
        return new PDO("mysql:host=$DB_ADDRESS;port=$DB_PORT;dbname=$DB_NAME", $DB_USER, $DB_PASS);
    } catch (PDOException $e) {
        // throw a custom error to make sure an error containing db info is not shown
        throw new Exception('Could not connect to the database.');
    }
}

function pdo_fah_connect()
{
    try {
        global $DB_ADDRESS, $DB_FAH_USER, $DB_FAH_PASS, $DB_FAH_NAME, $DB_PORT;
        return new PDO("mysql:host=$DB_ADDRESS;port=$DB_PORT;dbname=$DB_FAH_NAME", $DB_FAH_USER, $DB_FAH_PASS);
    } catch (PDOException $e) {
        // throw a custom error to make sure an error containing db info is not shown
        throw new Exception('Could not connect to the database.');
    }
}
