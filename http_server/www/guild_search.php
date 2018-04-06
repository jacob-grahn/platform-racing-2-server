<?php

require_once __DIR__ . '/../fns/output_fns.php';
require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/guilds/guild_name_to_id.php';
require_once __DIR__ . '/../queries/guilds/guild_select.php';
require_once __DIR__ . '/../queries/guilds/guild_select_members.php';
require_once __DIR__ . '/../queries/users/user_select_name_and_power.php';

$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

$guild_name = $_GET['name'];
$guild_id = (int) default_get('id', 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit("gui-guild-search-" . $ip, 5, 1, "Wait a bit before searching again.");
    rate_limit("gui-guild-search-" . $ip, 30, 5, "Wait a bit before searching again.");

    // sanity check: is any data entered?
    if (is_empty($guild_name) && is_empty($guild_id, false)) {
        output_header("Guild Search");
        output_search();
        output_footer();
        die();
    }

    // connect
    $pdo = pdo_connect();

    // if by name, get id
    if (!is_empty($guild_name) && is_empty($guild_id, false)) {
        $guild_id = (int) guild_name_to_id($pdo, $guild_name);
    }
    
    // start the page
    output_header("Guild Search");
    
    // output the search box
    output_search($guild_name, $guild_id);
    
    // center the page
    echo '<center>';
    
    // get guild info
    $guild = guild_select($pdo, $guild_id);
    
    // make some variables
    $guild_id = (int) $guild->guild_id;
    $guild_name = htmlspecialchars($guild->name);
    $creation_date = date('j/M/Y', $guild->creation_date);
    $active_date = date('j/M/Y', $guild->active_date);
    $member_count = (int) $guild->member_count;
    $emblem = $guild->emblem;
    $gp_today = (int) $guild->gp_total;
    $gp_total = (int) $guild->gp_today;
    $owner_id = (int) $guild->owner_id;
    $owner = user_select_name_and_power($pdo, $owner_id);
    $prose = htmlspecialchars($guild->note);
    $owner_name = htmlspecialchars($owner->name);
    $owner_url_name = htmlspecialchars(urlencode($owner->name));
    $owner_color = $group_colors[(int) $owner->power];
    $active_count = (int) guild_count_active($pdo, $guild_id);
    $members = guild_select_members($pdo, $guild_id);
    
    // check for .j instead of .jpg on the end of the emblem file name
    if (substr($emblem, -2) == '.j') {
        $emblem = str_replace('.j', '.jpg', $emblem);
    }
    
    // simple text replacement
    if ($member_count === 0) {
        $member_count = 'none';
    }
    
    // display guild info
    echo "<br>-- <b>$guild_name</b> --<br>";
    if (!is_empty($prose)) {
        echo "<span style='font-size: 11px; color: slategray;'><i>$prose</i></span><br>";
    }
    echo '<br>'
        ."<img src='https://pr2hub.com/emblems/$emblem'>"
        .'<br><br>'
        ."Owner: <a href='player_search.php?name=$owner_url_name' style='color: $owner_color; text-decoration: underline;'>$owner_name</a><br>"
        ."Members: $member_count ($active_count active)<br>"
        ."GP Today: $gp_today<br>"
        ."GP Total: $gp_total<br>"
        ."Created: $creation_date<br>"
        ."Last Active: $active_date<br>";

    // if members are in the guild, show the members
    if ($member_count >= 1) {
        // table header row
        echo '<br>'
            .'<table>'
            .'    <tr>
                      <th><b>Members</b></th>
                      <th><b>GP Today</b></th>
                      <th><b>GP Total</b></th>
                  </tr>';

        // make a new row for each member
        foreach ($members as $member) {
            $member_id = (int) $member->user_id;
            $member_name = htmlspecialchars($member->name);
            $member_url_name = htmlspecialchars(urlencode($member->name));
            $member_color = $group_colors[$member->power];
            $member_gp_today = (int) $member->gp_today;
            $member_gp_total = (int) $member->gp_total;
        
            // start new row, name column
            echo '<tr>'
                .'<td>';
            
            // if the guild owner, display a crown next to their name
            if ($member_id === $owner_id) {
                echo '<img src="/img/vault/Crown-40x40.png" height="12">';
            }
            
            // member name column
            echo "<a href='player_search.php?name=$member_url_name' style='color: #$member_color; text-decoration: underline;'>$member_name</a>"
                .'</td>';

            // gp today column
            echo '<td>'
                ."$gp_today"
                .'</td>';

            // gp total column
            echo '<td>'
                ."$gp_total"
                .'</td>';
            
            // end the row, move on to the next member
            echo '</tr>';
        }
    }
} catch(Exception $e) {
    $safe_error = htmlspecialchars($e->getMessage());
    echo "<br><i>Error: $safe_error</i><br>";
} finally {
    echo '</center>';
    output_footer();
    die();
}

function output_search($guild_name = '', $guild_id = '') {
    $guild_id = (int) $guild_id;

    // check if values passed are empty
    if (is_empty($guild_name)) $guild_name = '';
    if (is_empty($guild_id, false)) $guild_id = '';
    
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
        .'<input type="radio" onclick="name_id_check()" id="nameradio" name="typeRadio"> Name '
        .'<input type="radio" onclick="name_id_check()" id="idradio" name="typeRadio"> ID'
        .'<br>';
    
    // name form
    echo '<div id="nameform" style="display:none"><br>
              <form method="get">
                  Name: <input type="text" name="name" value="'.htmlspecialchars($guild_name).'">
                        <input type="submit" value="Search">
              </form>
          </div>';
          
    // id form
    echo '<div id="idform" style="display:none"><br>
              <form method="get">
                  ID: <input type="text" name="id" oninput="this.value = this.value.replace(/[^0-9.]/g, \'\').replace(/(\..*)\./g, \'$1\');" value="'.$guild_id.'">
                      <input type="submit" value="Search">
              </form>
          </div>';
    
    // end center
    echo '</center>';
}
