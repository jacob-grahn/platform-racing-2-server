<?php


// various mod/admin scripts, bans.php, leaderboard.php
function output_pagination($start, $count, $extra = '', $is_end = false)
{
    $next_start_num = $start + $count;
    $last_start_num = $start - $count;
    if ($last_start_num < 0) {
        $last_start_num = 0;
    }
    echo '<p>';
    if ($start > 0) {
        echo "<a href='?start=$last_start_num&count=$count$extra'><- Last</a> |";
    } else {
        echo '<- Last |';
    }
    if ($is_end === true) {
        echo(" Next ->");
    } else {
        echo " <a href='?start=$next_start_num&count=$count$extra'>Next -></a>";
    }
    echo '</p>';
}


// standard header
function output_header($title = '', $staff_nav = false, $show_admin = false, $call_jquery = false, $head_extras = [])
{
    global $header;

    // don't echo twice
    if (@$header) {
        return;
    } elseif (empty($header)) {
        $header = true;
    }

    echo "<!DOCTYPE html>"
        ."<html xmlns='http://www.w3.org/1999/xhtml'>"
        ."<head>"
            ."<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />"
            ."<meta name='viewport' content='width=device-width, initial-scale=1'>"
            ."<meta http-equiv='X-UA-Compatible' content='IE=edge' />"
            ."<title>PR2 Hub - $title</title>"
            ."<link href='/style/gwibble.css' rel='stylesheet' type='text/css' />"
            ."<link href='/style/pr2hub.css' rel='stylesheet' type='text/css'/>";

    foreach ($head_extras as $extra) {
        echo $extra;
    }

    if ($call_jquery || $staff_nav) {
        echo '<script src="https://code.jquery.com/jquery-latest.min.js"></script>'
            .'<script src="https://malsup.github.io/jquery.form.js"></script>';
    }

    // mod header
    if ($staff_nav === true) {
        echo '<script src="/scripts/mod.js"></script>';
        if ($show_admin === true) {
            echo '<script src="/scripts/admin.js"></script>';
        }
    }

    echo "</head>"
        ."<body>"
            ."<div id='container'>"
                ."<div id='header'></div>"
                ."<div id='body'>"
                    ."<div class='content'>";

    if ($staff_nav === true) {
        output_staff_nav($show_admin);
    }
}


// standard footer
function output_footer()
{
    $tz = date('T');
    $time = date("g:i a \o\\n M j, Y");
    echo "</div></div>"
        ."<div id='footer'>"
            ."<div class='content'>"
                ."<ul class='footer_links'>"
                    ."<li><a href='/backups'>Backups</a></li>"
                    ."<li><a href='https://jiggmin2.com/forums/showthread.php?tid=19'>Folding@Home</a></li>"
                    ."<li><a href='/terms_of_use.php'>Terms of Use</a></li>"
                    ."<li><a href='/rules'>Rules</a></li>"
                ."</ul>"
            ."<br />All times are $tz. The time is currently $time.</div>"
        ."</div></div>";
}


// mod/admin navigation
function output_staff_nav($formatting_for_admins = true)
{
    $reports_link = "<a href='/mod/reports.php?mode=messages'>Reports</a>";
    $log_link = "<a href='/mod/action_log.php?mode=mod'>Action Logs</a>";
    if (strpos($_SERVER['REQUEST_URI'], '/mod/reports.php') === 0) {
        $mode = default_get('mode', 'messages');
        $mode = $mode !== 'messages' && $mode !== 'levels' ? 'messages' : $mode;
        $other = $mode === 'messages' ? 'Levels' : 'Messages';
        $reports_url = '/mod/reports.php?mode=' . strtolower($other);
        $reports_link = str_replace($other, "<a href='$reports_url'>$other</a>", 'Reported: (Messages | Levels)');
    } elseif (strpos($_SERVER['REQUEST_URI'], '/mod/action_log.php') === 0) {
        $mode = default_get('mode', 'mod');
        $mode = $mode !== 'mod' && $mode !== 'prize' ? 'mod' : $mode;
        $other = $mode === 'mod' ? 'Prize' : 'Mod';
        $log_url = '/mod/action_log.php?mode=' . strtolower($other);
        $log_link = str_replace($other, "<a href='$log_url'>$other</a>", 'Logs: (Mod | Prize)');
    }

    echo "<p><b>"
        ."$reports_link - "
        ."<a href='/mod/player_info.php'>Player Search</a> - "
        ."<a href='/mod/ip_info.php'>IP Search</a> - "
        ."<a href='/bans/bans.php'>Ban Log</a> - "
        .$log_link;

    if ($formatting_for_admins === true) {
        echo "<br>"
            ."<a href='/admin/player_deep_info.php'>Update Account</a> - "
            ."<a href='/admin/guild_deep_info.php'>Update Guild</a> - "
            ."<a href='/admin/level_deep_info.php'>Update Level</a> - "
            ."<a href='/admin/set_campaign.php'>Set Custom Campaign</a> - "
            ."<a href='/admin/admin_log.php'>Admin Action Logs</a>";
    }

    echo '</b></p>'
        .'<p>---</p>';
}


function output_error_page($message, $staff = null, $page_title = 'Error')
{
    // determine power from staff variable
    $mod = $admin = false;
    if (!isset($staff)) {
    } elseif (isset($staff->mod)) {
        $mod = $staff->mod;
        $admin = $staff->admin;
    } elseif (isset($staff->power)) {
        $mod = $staff->power >= 2;
        $admin = $staff->power == 3;
    }

    // show error
    output_header($page_title, $mod, $admin);
    $error = htmlspecialchars($message, ENT_QUOTES);
    echo !empty($error) ? "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>" : '';
}
