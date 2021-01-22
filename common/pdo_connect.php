<?php


/**
 * Establishes a database connection.
 *
 * @throws Exception if the connection fails.
 */
function pdo_connect()
{
    try {
        global $DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT, $DEBUG_MODE;
        $pdo = new PDO("mysql:host=$DB_ADDRESS;port=$DB_PORT;dbname=$DB_NAME", $DB_USER, $DB_PASS);
        if ($DEBUG_MODE) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        }
        return $pdo;
    } catch (PDOException $e) {
        // throw a custom error to make sure an error containing db info is not shown
        throw new Exception($DEBUG_MODE ? $e->getMessage() : 'Could not connect to the database.');
    }
}


/**
 * Sets the character encoding for all queries on this PDO connection.
 *
 * @param resource pdo Contains the current database connection instance.
 * @param string encoding The encoding to set.
 *
 * @throws Exception if the query fails.
 */
function db_set_encoding($pdo, $encoding)
{
    $stmt = $pdo->prepare('SET NAMES :encoding');
    $stmt->bindValue(':encoding', $encoding);
    if ($stmt->execute() === false) {
        throw new Exception('Invalid charset.');
    }
}
