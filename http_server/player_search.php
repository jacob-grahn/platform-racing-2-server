<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/player_search_fns.php';

$name = default_get('name', '');
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
    if ($user === false) {
        throw new Exception("Could not find a user with that name.");
    }

    // output
    output_search($name);
    output_page($pdo, $user);
} catch (Exception $e) {
    $safe_error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    output_search($name);
    echo "<br /><i>Error: $safe_error</i>";
} finally {
    output_footer();
}
