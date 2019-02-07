<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/set_campaign_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/campaigns.php';

$action = default_post('action', 'lookup');
$message = htmlspecialchars(default_get('message', ''));
$campaign_id = 6; // 1 = Original, 2 = Speed, 3 = Luna, 4 = Timeline, 5 = Legendary, 6 = Custom

try {
    // rate limiting
    rate_limit('set-campaign-'.$ip, 60, 10);
    rate_limit('set-campaign-'.$ip, 10, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, null, true, 3);

    // lookup
    if ($action === 'lookup') {
        output_form($pdo, $message, $campaign_id);
    } // update
    elseif ($action === 'update') {
        update($pdo, $admin, $campaign_id);
    } // this should never happen
    else {
        throw new Exception("Invalid action specified.");
    }
} catch (Exception $e) {
    output_header('Error');
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
