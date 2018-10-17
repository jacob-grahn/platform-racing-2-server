<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/guild_deep_info_fns.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_select_members.php';
require_once QUERIES_DIR . '/guild_transfers/guild_transfers_select_by_guild.php';

$guild_id = (int) find('guild_id', 0);

try {
    // rate limiting
    rate_limit('guild-deep-info-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('guild-deep-info-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $mod = check_moderator($pdo, false, 3);

    if ($guild_id == 0) {
        $guild_id = '';
    }

    output_header('Guild Deep Info', true, true);

    echo '<form name="input" action="" method="get">';
    echo "Guild ID: <input type='text' name='guild_id' value='$guild_id'>&nbsp;";
    echo '<input type="submit" value="Submit"><br>';
    if (!is_empty($guild_id, false)) {
        try {
            $guild = guild_select($pdo, $guild_id);
            $owner_transfers = guild_transfers_select_by_guild($pdo, $guild_id, true);
            $members = guild_select_members($pdo, $guild_id, true);
            output_object($guild);
            output_objects($owner_transfers);
            output_objects($members);
            $guild_id = (int) $guild->guild_id;
            echo "<a href='update_guild.php?guild_id=$guild_id'>edit</a><br><br><br>";
        } catch (Exception $e) {
            $error = htmlspecialchars($e->getMessage, ENT_QUOTES);
            echo "<i>Error: $error</i><br><br>";
        }
    }

    echo '</form>';
} catch (Exception $e) {
    output_header('Error');
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    echo "Error: $error";
} finally {
    output_footer();
}
