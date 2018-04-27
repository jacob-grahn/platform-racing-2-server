<?php

// TO-DO: is this needed?
function all_optimize($pdo, $DB_NAME)
{
    // get all table names
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // count the tables
    $tables_count = count($tables);
    $end = $tables_count - 1;
    
    // put the table names into an array
    $table_names = array();
    foreach (range(0, $end) as $num) {
        $table_name = $tables[$num]["Tables_in_".$DB_NAME]; // take from env
        array_push($table_names, $table_name);
    }
    
    // join the table names
    $tables = join(", ", $table_names);
    
    // execute one SQL query that optimizes every db table at once
    $pdo->exec("OPTIMIZE TABLE $tables");
}
