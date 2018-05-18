<?php

header("Content-type: text/plain");
require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/friends/friends_select.php';
require_once QUERIES_DIR . '/ignored/ignored_select_list.php';

$mode = find_no_cookie('mode');
$ip = get_ip();

try {
    // rate limiting
    rate_limit("user-list-$ip", 5, 2);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo);
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $mode = htmlspecialchars($mode);
        throw new Exception(
            "Guests can't add players to their $mode list. ".
            "To access this feature, please create your own account."
        );
    }

    // more rate limiting
    rate_limit("user-list-$user_id", 5, 2);

    switch ($mode) {
        case 'friends':
            $users = friends_select($pdo, $user_id);
            break;
        case 'ignored':
            $users = ignored_select_list($pdo, $user_id);
            break;
        default:
            throw new Exception("Invalid list mode specified.");
    }

    // make individual list entries
    $num = 0;
    foreach ($users as $row) {
        $name = urlencode(htmlspecialchars($row->name));
        $group = $row->power;
        $status = $row->status;
        $rank = $row->rank;

        if (isset($row->used_tokens)) {
            $used_tokens = $row->used_tokens;
        } else {
            $used_tokens = 0;
        }

        $active_rank = $rank + $used_tokens;
        $hats = count(explode(',', $row->hat_array)) - 1;

        if (strpos($status, 'Playing on ') !== false) {
            $status = substr($status, 11);
        }

        if ($num > 0) {
            echo "&";
        }

        echo ("name$num=$name"
        ."&group$num=$group"
        ."&status$num=$status"
        ."&rank$num=$active_rank"
        ."&hats$num=$hats");
        $num++;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
