<?php


//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function output_header($title = '', $formatting_for_mods = false, $formatting_for_admins = false)
{
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PR2 Hub - <?php echo $title; ?></title>
    <link href="//pr2hub.com/style/gwibble.css" rel="stylesheet" type="text/css" />
    <link href="//pr2hub.com/style/pr2hub.css" rel="stylesheet" type="text/css"/>
    <?php if ($formatting_for_mods) { ?>
        <script src="https://code.jquery.com/jquery-latest.min.js"></script>
        <script src="https://malsup.github.io/jquery.form.js"></script>
    <?php } ?>
</head>

<body>

<div id="container">

    <div id="header">
    </div>

    <div id="body">
            <div class="content">

<?php

if ($formatting_for_mods) {
    output_mod_navigation($formatting_for_admins);
}
}


function output_footer()
{
?>

        </div>
    </div>

    <div id="footer">
        <ul class="footer_links">
            <li><a href="//pr2hub.com/backups" target="_blank">Backups</a></li>
            <li><a href="https://jiggmin2.com/forums/showthread.php?tid=19" target="_blank">Folding@Home</a></li>
            <li><a href="//pr2hub.com/terms_of_use.php" target="_blank">Terms of Use</a></li>
            <li><a href="https://jiggmin2.com/forums/showthread.php?tid=385" target="_blank">Rules</a></li>
        </ul>
    </div>
</div>

<?php
}


function output_mod_navigation($formatting_for_admins = true)
{
?>

    <p>
        <b>
            <a href="//pr2hub.com/mod/reported_messages.php" target="_blank">Reported Messages</a>
            -
            <a href="//pr2hub.com/mod/player_search.php" target="_blank">Player Search</a>
            -
            <a href="//pr2hub.com/bans/bans.php" target="_blank">Ban Log</a>
            -
            <a href="//pr2hub.com/mod/mod_log.php" target="_blank">Mod Action Log</a>
    <?php if ($formatting_for_admins) { ?>
            <br>
            <a href="//pr2hub.com/admin/player_deep_info.php" target="_blank">Update Account</a>
            -
            <a href="//pr2hub.com/admin/guild_deep_info.php" target="_blank">Update Guild</a>
            -
            <a href="//pr2hub.com/admin/set_campaign.php" target="_blank">Set Custom Campaign</a>
            -
            <a href="//pr2hub.com/admin/admin_log.php" target="_blank">Admin Action Log</a>
    <?php } ?>
            
        </b>
    </p>
    <p>---</p>

<?php
}
?>
