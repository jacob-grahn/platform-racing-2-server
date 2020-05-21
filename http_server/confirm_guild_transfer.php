<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/guild_transfers.php';
require_once QUERIES_DIR . '/servers.php';

$code = default_get('code', '');
$ip = get_ip();

try {
    output_header('Confirm Guild Ownership Transfer');

    // sanity check: check for a confirmation code
    if (is_empty($code)) {
        throw new Exception('No code found.');
    }

    // rate limiting
    rate_limit('confirm-guild-transfer-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();

    // get the pending change information
    $row = guild_transfer_select($pdo, $code);
    $guild_id = (int) $row->guild_id;
    $new_owner_id = (int) $row->new_owner_id;
    $transfer_id = (int) $row->transfer_id;

    // get updated guild data
    $guild = guild_select($pdo, $guild_id);

    // do the transfer
    guild_transfer_complete($pdo, $transfer_id, $ip);
    guild_update($pdo, $guild_id, $guild->guild_name, $guild->emblem, $guild->note, $new_owner_id);

    // tell the world
    $safe_guild_name = htmlspecialchars($guild->guild_name, ENT_QUOTES);
    $safe_new_owner = htmlspecialchars(id_to_name($pdo, $new_owner_id), ENT_QUOTES);
    echo "Great success! The new owner of $safe_guild_name is $safe_new_owner. Long live $safe_guild_name!";

    // tell the socket
    $ret = new stdClass();
    $ret->transferring = true;
    $ret->guild_id = $guild_id;
    $ret->guild_name = $guild->guild_name;
    $ret->owner_id = $new_owner_id;
    $ret->changer_id = (int) $row->old_owner_id;
    @poll_servers(servers_select($pdo), 'guild_change`' . json_encode($ret));
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    echo "Error: $error";
} finally {
    output_footer();
}
