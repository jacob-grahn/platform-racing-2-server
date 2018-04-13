<?php

require_once __DIR__ . '/../fns/output_fns.php';
require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/player_search_fns.php';
require_once __DIR__ . '/../queries/users/user_select_expanded.php';
require_once __DIR__ . '/../queries/guilds/guild_select.php';

$name = find_no_cookie("name", "");
$ip = get_ip();

output_header("Player Search");

if (is_empty($name)) {
    output_search();
    output_footer();
    die();
}

try {
    // rate limiting
    rate_limit("gui-player-search-" . $ip, 5, 1, "Wait a bit before searching again.");
    rate_limit("gui-player-search-" . $ip, 30, 5, "Wait a bit before searching again.");

    // connect
    $pdo = pdo_connect();

    // find user
    $user = find_user($pdo, $name);

    // output
    output_search($name);
    output_page($pdo, $user);
    output_footer();
} catch (Exception $e) {
    $safe_error = htmlspecialchars($e->getMessage());
    output_search($name);
    echo "<br /><i>Error: $safe_error</i>";
    output_footer();
    die();
}
