<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/bans/ban_select.php';

$ban_id = (int) default_val($_GET['ban_id'], 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-lift-ban-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error');
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // output header
    output_header('Lift Ban', true);

    // get the ban
    $ban = ban_select($pdo, $ban_id);
    $banned_name = $ban->banned_name;
    $lifted = $ban->lifted;
    if ($lifted == '1') {
        throw new Exception('This ban has already been lifted.');
    }

    // make the visible things...
    echo "<p>To lift the ban on $banned_name, please enter a reason and hit submit.</p>";

    ?>

    <form action="do_lift_ban.php" method="post">
        <input type="hidden" value="<?php echo $ban_id; ?>" name="ban_id"  />
        <input type="text" value="They bribed me with skittles!" name="reason" size="70" />
        <input type="submit" value="Lift Ban" />
    </form>


    <?php
} catch (Exception $e) {
    // header already echoed
    $error = $e->getMessage();
    echo "Error: $error";
    output_footer();
}

?>
