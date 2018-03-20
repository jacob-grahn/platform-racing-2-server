<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

$message = default_get('message', '');
$ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-player-search-'.$ip, 5, 3);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo, false);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // header
    output_header('Player Search', true);

    // error message
    if (!is_empty($message)) {
        echo "<p><b>$message</b></p>";
    }

    ?>
    <form action="do_player_search.php" method="post">
        Name <input type="text" value="" name="name" />
        <input type="submit" value="Search" />
    </form>
    <?php

    // footer
    output_footer();
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error";
    output_footer();
}

?>
