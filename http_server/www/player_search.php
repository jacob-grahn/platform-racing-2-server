<?php

require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pages/player_search_fns.php';
require_once QUERIES_DIR . '/users/user_select_expanded.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';

$name = find_no_cookie("name", "");
$ip = get_ip();

output_header("Player Search");

if (is_empty($name)) {
    output_search();
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
} catch (Exception $e) {
    $safe_error = htmlspecialchars($e->getMessage());
    output_search($name);
    echo "<br /><i>Error: $safe_error</i>";
} finally {
    output_footer();
    die();
}
