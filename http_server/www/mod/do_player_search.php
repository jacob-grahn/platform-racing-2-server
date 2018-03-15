<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

$name = default_val($_POST['name'], '');
$ip = get_ip();

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('mod-do-player-search-'.$ip, 5, 2);

    // connect
    $db = new DB();
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
}

try {
    // sanity check
    if (is_empty($name)) {
        throw new Exception('No username specified.');
    }

    // look for a player with provided name
    $user_id = name_to_id($db, $name);

    // redirect
    header("Location: player_info.php?user_id=$user_id");
    die();
} catch (Exception $e) {
    $error = urlencode($e->getMessage());
    header("Location: player_search.php?message=$error");
    die();
}
