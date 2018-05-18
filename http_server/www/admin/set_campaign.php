<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/set_campaign_fns.php';
require_once QUERIES_DIR . '/campaign/campaign_select_by_id.php';
require_once QUERIES_DIR . '/campaign/campaign_update.php';
require_once QUERIES_DIR . '/levels/level_select.php';
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';

$action = $_POST['action'];
$message = htmlspecialchars(find('message', ''));
$campaign_id = 6; // 1 = Original, 2 = Speed, 3 = Luna, 4 = Timeline, 5 = Legendary, 6 = Custom

// if empty or not set
if (is_empty($action)) {
    $action = "lookup";
}

try {
    // rate limiting
    rate_limit('set-campaign-'.$ip, 60, 5);
    rate_limit('set-campaign-'.$ip, 10, 1);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, true, 3);

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
    echo 'Error: ' . $e->getMessage();
} finally {
    output_footer();
    die();
}
