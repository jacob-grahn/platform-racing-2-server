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
function output_header($title = '', $staff_nav = false, $show_admin = false)
{
    echo "<!DOCTYPE html>"
        ."<html xmlns='http://www.w3.org/1999/xhtml'>"
        ."<head>"
            ."<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />"
            ."<title>PR2 Hub - $title</title>"
            ."<link href='/style/gwibble.css' rel='stylesheet' type='text/css' />"
            ."<link href='/style/pr2hub.css' rel='stylesheet' type='text/css'/>"
            ."<script src='/style/menu.js' type='text/javascript'></script>";
    
    // mod header
    if ($staff_nav === true) {
        echo "<script src='https://code.jquery.com/jquery-latest.min.js'></script>"
            ."<script src='https://malsup.github.io/jquery.form.js'></script>";
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
    echo "</div></div>"
        ."<div id='footer'>"
            ."<ul class='footer_links'>"
                ."<li><a href='//pr2hub.com/backups'>Backups</a></li>"
                ."<li><a href='https://jiggmin2.com/forums/showthread.php?tid=19'>Folding@Home</a></li>"
                ."<li><a href='//pr2hub.com/terms_of_use.php'>Terms of Use</a></li>"
                ."<li><a href='https://jiggmin2.com/forums/showthread.php?tid=385'>Rules</a></li>"
            ."</ul>"
        ."</div></div>";
}

// mod/admin navigation
function output_staff_nav($formatting_for_admins = true)
{
    echo "<p><b>"
        ."<a href='//pr2hub.com/mod/reported_messages.php'>Reported Messages</a> - "
        ."<a href='//pr2hub.com/mod/player_info.php'>Player Search</a> - "
        ."<a href='//pr2hub.com/bans/bans.php'>Ban Log</a> - "
        ."<a href='//pr2hub.com/mod/mod_log.php'>Mod Action Log</a>";
    
    if ($formatting_for_admins === true) {
        echo "<br>"
            ."<a href='//pr2hub.com/admin/player_deep_info.php'>Update Account</a> - "
            ."<a href='//pr2hub.com/admin/guild_deep_info.php'>Update Guild</a> - "
            ."<a href='//pr2hub.com/admin/set_campaign.php'>Set Custom Campaign</a> - "
            ."<a href='//pr2hub.com/admin/admin_log.php'>Admin Action Log</a>";
    }
    
    echo '</b></p>'
        .'<p>---</p>';
}
