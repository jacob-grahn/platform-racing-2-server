<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/bans.php';
require_once QUERIES_DIR . '/mod_actions.php';

$ban_id = (int) default_get('ban_id', 0);
$ip = get_ip();

// for lifting
$action = default_post('action', 'form');
$reason = default_post('reason', 'They bribed me with skittles!');
$reason = $reason . ' @' . date('M j, Y g:i A'); // add time/date to lift reason

try {
    // rate limiting
    rate_limit('mod-lift-ban-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);

    // lift
    if ($action === 'lift' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ban_id = (int) default_post('ban_id', 0);

        // sanity check: are any values blank?
        if (is_empty($ban_id, false) || is_empty($reason)) {
            throw new Exception('Some information is missing.');
        }

        // lift the ban
        $lifted_by = $mod->name;
        ban_lift($pdo, $ban_id, $lifted_by, $reason);

        // record the lifting
        $user_id = (int) $mod->user_id;
        $name = $mod->name;
        $html_reason = htmlspecialchars($reason, ENT_QUOTES);
        mod_action_insert($pdo, $user_id, "$name lifted ban $ban_id from $ip. Reason: $html_reason.", 0, $ip);

        // redirect to a page showing the lifted ban
        header("Location: /bans/show_record.php?ban_id=$ban_id");
        die();
    } else {
        // get the ban
        $ban = ban_select($pdo, $ban_id);
        $banned_name = $ban->banned_name;
        if ((int) $ban->lifted === 1) {
            throw new Exception('This ban has already been lifted.');
        }

        // --- make the visible things --- \\
        output_header('Lift Ban', $mod->power >= 2, (int) $mod->power === 3);
        echo "<p>To lift the ban on $banned_name, please enter a reason and hit submit.</p>";
        echo '<form method="post">'
            ."<input type='hidden' value='$ban_id' name='ban_id'>"
            .'<input type="hidden" value="lift" name="action">'
            .'<input type="text" value="They bribed me with skittles!" name="reason" size="70">'
            .'&nbsp;<input type="submit" value="Lift Ban">'
            .'</form>';
    }
} catch (Exception $e) {
    output_header('Error', $mod->power >= 2, (int) $mod->power === 3);
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
