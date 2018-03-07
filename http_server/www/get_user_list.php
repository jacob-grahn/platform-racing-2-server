<?php

header("Content-type: text/plain");
require_once '../fns/all_fns.php';

$mode = find_no_cookie('mode');
$ip = get_ip();

try {
    
    switch($mode) {
    case 'friends':
        $table = 'friends';
        $var = 'friend_id';
        break;
    case 'ignored':
        $table = 'ignored';
        $var = 'ignore_id';
        break;
    default:
        throw new Exception("Invalid list mode specified.");
    }
    
    // rate limiting
    rate_limit("user-list-$table-$ip", 5, 2);
    
    // connect
    $db = new DB();
    
    // check their login
    $user_id = token_login($db);
    
    // more rate limiting
    rate_limit("user-list-$table-$user_id", 5, 2);
    
    // get the information from the database
    $result = $db->query(
        "SELECT users.name, users.power, users.status, pr2.rank, pr2.hat_array, rank_tokens.used_tokens
									FROM $table
									INNER JOIN users
									ON users.user_id = $table.$var
									LEFT JOIN pr2
									ON users.user_id = pr2.user_id
									LEFT JOIN rank_tokens
									ON users.user_id = rank_tokens.user_id
									WHERE $table.user_id = '$user_id'
									LIMIT 0, 250"
    );
    
    if(!$result) {
        throw new Exception('Could not retrieve player list.');
    }
    
    $num = 0;

    // make individual list entries
    while($row = $result->fetch_object()){
        $name = urlencode(htmlspecialchars($row->name));
        $group = $row->power;
        $status = $row->status;
        $rank = $row->rank;
        
        if(isset($row->used_tokens)) {
            $used_tokens = $row->used_tokens;
        }
        else {
            $used_tokens = 0;
        }
        
        $active_rank = $rank + $used_tokens;
        $hats = count(explode(',', $row->hat_array)) - 1;
        
        if(strpos($status, 'Playing on ') !== false) {
            $status = substr($status, 11);
        }
        
        if($num > 0) {
            echo "&";
        }
        
        echo ("name$num=$name"
        ."&group$num=$group"
        ."&status$num=$status"
        ."&rank$num=$active_rank"
        ."&hats$num=$hats");
        $num++;
    }
}

catch(Exception $e){
    $error = $e->getMessage();
    echo "error=$error";
}

?>
