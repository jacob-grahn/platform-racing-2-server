<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

$ban_id = (int) default_val($_GET['ban_id'], 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-lift-ban-'.$ip, 5, 2);

    // connect
    $db = new DB();

    // make sure you're a moderator
    $mod = check_moderator($db);
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
    $result = $db->query(
        "SELECT *
								 	FROM bans
									WHERE ban_id = '$ban_id'
									LIMIT 0, 1"
    );
    if (!$result) {
        throw new Exception("Could not get the ban's data from the database.");
    }
    if ($result->num_rows <= 0) {
        throw new Exception("Ban ID #$ban_id doesn't exist.");
    }

    // get ban info
    $row = $result->fetch_object();
    $banned_name = $row->banned_name;
    $lifted = $row->lifted;
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
