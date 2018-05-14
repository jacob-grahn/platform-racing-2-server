<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/bans/ban_select.php';

$ban_id = (int) $_GET['ban_id'];
$ip = get_ip();

try {
    // rate limiting
    rate_limit('show-ban-record-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // are they a moderator
    $is_mod = is_moderator($pdo, false);
    if ($is_mod === false) {
        rate_limit('list-bans-'.$ip, 60, 10, "Please wait at least one minute before trying to view another ban.");
    }

    // get the ban details
    $row = ban_select($pdo, $ban_id);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error Fetching Ban', $is_mod);
    echo "Error: $error";
    output_footer();
    die();
}

// output header (w/ mod nav if they're a mod)
output_header('View Ban', $is_mod);

//--- output the page ---
$ban_id = $row->ban_id;
$banned_ip = $row->banned_ip;
$mod_user_id = $row->mod_user_id;
$banned_user_id = $row->banned_user_id;
$time = $row->time;
$expire_time = $row->expire_time;
$reason = $row->reason;
$record = $row->record;
$mod_name = $row->mod_name;
$banned_name = $row->banned_name;
$lifted = $row->lifted;
$lifted_by = $row->lifted_by;
$lifted_reason = $row->lifted_reason;
$ip_ban = $row->ip_ban;
$account_ban = $row->account_ban;
$notes = $row->notes;

$formatted_time = date('M j, Y g:i A', $time);
$expire_formatted_time = date('M j, Y g:i A', $expire_time);
$duration = $expire_time - $time;
$f_duration = format_duration($duration);

$display_name = '';
if ($account_ban == 1) {
    $display_name .= $banned_name;
}
if ($ip_ban == 1 && $is_mod) {
    if ($display_name != '') {
        $display_name .= ' ';
    }
    $display_name .= "[$banned_ip]";
}

$html_lifted_by = htmlspecialchars($lifted_by);
$html_lifted_reason = htmlspecialchars($lifted_reason);
$html_mod_name = htmlspecialchars($mod_name);
$html_banned_name = htmlspecialchars($display_name);
$html_reason = htmlspecialchars($reason);
$html_record = str_replace("\r", '<br/>', htmlspecialchars($record));
$html_notes = str_replace("\n", '<br>', htmlspecialchars($notes));


if ($lifted == 1) {
    echo '<b><p>-----------------------------------------------------------------------------------------------</p>'
           ."<p>--- This ban has been lifted by $html_lifted_by ---</p>"
           ."<p>--- Reason: $html_lifted_reason ---</p>"
           .'<p>-----------------------------------------------------------------------------------------------</p>'
           .'<p>&nbsp;</p></b>';
}



//make the names clickable for moderators
if ($is_mod === true) {
    $html_mod_name = "<a href='/mod/player_info.php?user_id=$mod_user_id'>$html_mod_name</a>";
    if ($banned_user_id != 0 && $account_ban == 1) {
        $html_banned_name = "<a href='/mod/player_info.php?user_id=$banned_user_id'>$html_banned_name</a>";
    } else {
        $html_banned_name = "<a href='/mod/ip_info.php?ip=$banned_ip'>$html_banned_name</a>";
    }
}


echo "<p>$html_mod_name banned $html_banned_name for $f_duration on $formatted_time.</p>
        <p>Reason: $html_reason</p>
        <p>This ban will expire on $expire_formatted_time.</p>
        <p> --- </p>
        <p>$html_record</p>
        <p> --- </p>";

if ($is_mod === true) {
    if (isset($notes) && $notes != '') {
        echo "<p> --- notes</p>";
        echo "<p>$html_notes</p>";
        echo "<p> ---</p>";
    }
    if ($lifted != 1) {
        echo "<p><a href='/mod/ban_edit.php?ban_id=$ban_id'>Edit Ban</a></p>";
        echo "<p><a href='/mod/lift_ban.php?ban_id=$ban_id'>Lift Ban</a></p>";
    }
}

echo '<p><a href="bans.php">Go Back</a></p>';

output_footer();
die();
