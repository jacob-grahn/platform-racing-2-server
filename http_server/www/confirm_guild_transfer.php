<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/guild_transfers/guild_transfer_select.php';
require_once QUERIES_DIR . '/guild_transfers/guild_transfer_complete.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_update.php';

$code = $_GET['code'];
$ip = get_ip();

try {
    output_header('Confirm Guild Ownership Transfer');

    // sanity check: check for a confirmation code
    if (!isset($code)) {
        throw new Exception('No code found.');
    }

    // rate limiting
    rate_limit('confirm-guild-transfer-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();

    // get the pending change information
    $row = guild_transfer_select($pdo, $code);
    $guild_id = $row->guild_id;
    $new_owner_id = $row->new_owner_id;
    $transfer_id = $row->transfer_id;

    // get updated guild data
    $guild = guild_select($pdo, $guild_id);

    // do the transfer
    guild_transfer_complete($pdo, $transfer_id, $ip);
    guild_update($pdo, $guild_id, $guild->guild_name, $guild->emblem, $guild->note, $new_owner_id);

    // tell the world
    $safe_guild_name = htmlspecialchars($guild->guild_name);
    $safe_new_owner = htmlspecialchars(id_to_name($pdo, $new_owner_id));
    echo "Great success! The new owner of $safe_guild_name is $safe_new_owner. Long live $safe_guild_name!";
} catch (Exception $e) {
    $message = $e->getMessage();
    echo "Error: $message";
} finally {
    output_footer();
    die();
}
