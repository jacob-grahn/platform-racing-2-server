<?php

// guild_search.php
function output_guild_search($guild_name = '', $guild_id = '', $mode = null)
{
    $guild_id = (int) $guild_id;

    // choose which one to set after searching
    $id_display = 'none';
    $name_display = 'none';
    $id_checked = '';
    $name_checked = '';
    switch ($mode) {
        case 'id':
            $id_display = 'block';
            $id_checked = 'checked="checked"';
            break;
        case 'name':
            $name_display = 'block';
            $name_checked = 'checked="checked"';
            break;
    }

    // check if values passed are empty
    if (is_empty($guild_name)) {
        $guild_name = '';
    }
    if (is_empty($guild_id, false)) {
        $guild_id = '';
    }

    // center
    echo '<center>';

    // gwibble, spacing
    echo '<font face="Gwibble" class="gwibble">-- Guild Search --</font><br><br>';

    // javascript to show/hide the name/id textboxes
    echo '<script>
              function name_id_check() {
                  if (document.getElementById("nameradio").checked) {
                      document.getElementById("nameform").style.display = "block";
                      document.getElementById("idform").style.display = "none";
                  }
                  else if (document.getElementById("idradio").checked) {
                  document.getElementById("idform").style.display = "block";
                  document.getElementById("nameform").style.display = "none";
                  }
              }
          </script>';

    // search type selection
    echo 'Search by: '
        ."<input type='radio' onclick='name_id_check()' id='nameradio' name='typeRadio' $name_checked> Name "
        ."<input type='radio' onclick='name_id_check()' id='idradio' name='typeRadio' $id_checked> ID"
        .'<br>';

    // name form
    $html_guild_name = htmlspecialchars($guild_name);
    echo "<div id='nameform' style='display:$name_display'><br>
              <form method='get'>
                  Name: <input type='text' name='name' value='$html_guild_name'>
                        <input type='submit' value='Search'>
              </form>
          </div>";

    // id form
    echo "<div id='idform' style='display:$id_display'><br>
              <form method='get'>
                  ID:
                  <input type='text'
                         name='id'
                         oninput=\"this.value = this.value.replace(/[^0-9.]/g, \'\').replace(/(\..*)\./g, \'$1\');\"
                         value='$guild_id'>
                  <input type='submit' value='Search'>
              </form>
          </div>";

    // end center
    echo '</center>';
}

// bans.php
function create_ban_list($bans)
{
    $str = '<p><ul>';
    foreach ($bans as $row) {
        $ban_date = date("F j, Y, g:i a", $row->time);
        $reason = htmlspecialchars($row->reason);
        $ban_id = $row->ban_id;
        $str .= "<li><a href='../bans/show_record.php?ban_id=$ban_id'>$ban_date:</a> $reason";
    }
    $str .= '</ul></p>';
    return $str;
}

// various mod/admin scripts, bans.php, leaderboard.php
function output_pagination($start, $count, $extra = '', $is_end = false)
{
    $next_start_num = $start + $count;
    $last_start_num = $start - $count;
    if ($last_start_num < 0) {
        $last_start_num = 0;
    }
    echo('<p>');
    if ($start > 0) {
        echo("<a href='?start=$last_start_num&count=$count$extra'><- Last</a> |");
    } else {
        echo('<- Last |');
    }
    if ($is_end === true) {
        echo(" Next ->");
    } else {
        echo(" <a href='?start=$next_start_num&count=$count$extra'>Next -></a>");
    }
    echo('</p>');
}


// standard header
function output_header($title = '', $formatting_for_mods = false, $formatting_for_admins = false)
{
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PR2 Hub - <?php echo $title; ?></title>
    <link href="//pr2hub.com/style/gwibble.css" rel="stylesheet" type="text/css" />
    <link href="//pr2hub.com/style/pr2hub.css" rel="stylesheet" type="text/css"/>
    <script src="//pr2hub.com/pr2hub_menu.js" type="text/javascript"></script>
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

// standard footer
function output_footer()
{
?>

        </div>
    </div>

    <div id="footer">
        <ul class="footer_links">
            <li><a href="//pr2hub.com/backups">Backups</a></li>
            <li><a href="https://jiggmin2.com/forums/showthread.php?tid=19">Folding@Home</a></li>
            <li><a href="//pr2hub.com/terms_of_use.php">Terms of Use</a></li>
            <li><a href="https://jiggmin2.com/forums/showthread.php?tid=385">Rules</a></li>
        </ul>
    </div>
</div>

<?php
}

// mod/admin navigation
function output_mod_navigation($formatting_for_admins = true)
{
?>

    <p>
        <b>
            <a href="//pr2hub.com/mod/reported_messages.php">Reported Messages</a>
            -
            <a href="//pr2hub.com/mod/player_info.php">Player Search</a>
            -
            <a href="//pr2hub.com/bans/bans.php">Ban Log</a>
            -
            <a href="//pr2hub.com/mod/mod_log.php">Mod Action Log</a>
    <?php if ($formatting_for_admins) { ?>
            <br>
            <a href="//pr2hub.com/admin/player_deep_info.php">Update Account</a>
            -
            <a href="//pr2hub.com/admin/guild_deep_info.php">Update Guild</a>
            -
            <a href="//pr2hub.com/admin/set_campaign.php">Set Custom Campaign</a>
            -
            <a href="//pr2hub.com/admin/admin_log.php">Admin Action Log</a>
    <?php } ?>

        </b>
    </p>
    <p>---</p>

<?php
}
?>
