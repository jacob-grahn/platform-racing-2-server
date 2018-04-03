<?php
require_once __DIR__ . '/../fns/output_fns.php';
require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/guilds/guild_select.php';
require_once __DIR__ . '/../queries/guilds/guild_select_by_name.php';
require_once __DIR__ . '/../queries/guilds/guild_select_members.php';

function output_page() {
    echo '
    <center>
    <font face="Gwibble" class="gwibble">-- Guild Search --</font>
    <br />
    <br />
    <script>
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
    </script>
    Search by: 
    <input type="radio" onclick="name_id_check()" id="nameradio" name="typeRadio">
    Name 
    <input type="radio" onclick="name_id_check()" id="idradio" name="typeRadio">
    ID
    <br />
    <div id="nameform" style="display:none">
    <br />
       <form method="get">
          Name:
          <input type="text" name="name">
          <input type="submit" value="Search">
       </form>
    </div>
    <div id="idform" style="display:none">
    <br />
       <form method="get">
          ID:
          <input type="text" name="id" oninput="this.value = this.value.replace(/[^0-9.]/g, \'\').replace(/(\..*)\./g, \'$1\');" />
          <input type="submit" value="Search">
       </form>
    </div>';
}

$guild_name = find_no_cookie('name', '');
$guild_id = find_no_cookie('id', 0);
$ip = get_ip();

output_header("Guild Search");

output_page();

if (is_empty($name && $id)) {
    output_footer();
    die();
}
elseif (isset($guild_name, $guild_id)) {
    echo "
    <br />
    <i>Error: You cannot enter both guild name and ID.</i>
    <br />";
    output_footer();
    die();
}
elseif (!is_numeric($_GET["id"]) && is_empty($_GET["name"])) {
    echo "
    <br />
    <i>Error: ID should be numeric.</i>
    <br />";
    output_footer();
    die();
}

try {

    rate_limit("gui-guild-search-" . $ip, 5, 1, "Wait a bit before searching again.");
    rate_limit("gui-guild-search-" . $ip, 30, 5, "Wait a bit before searching again.");

    $pdo = pdo_connect();

    if ($guild_id > 0) {
        $guild = guild_select($pdo, $guild_id);
    }
    else {
        $guild = guild_select_by_name($pdo, $guild_name);
        $guild_id = $guild->guild_id;
    }

    $safe_guild_name = htmlspecialchars($guild->name);
    $emblem = $guild->emblem;
    $guild_id = (int)$guild->guild_id;
    $creation_date = $guild->creation_date;
    $gp_today = (int)$guild->gp_total;
    $gp_total = (int)$guild->gp_today;
    $member_count = (int)$guild->member_count;
    $active_count = (int)$guild->active_count = guild_count_active($pdo, $guild->guild_id);
    $prose = htmlspecialchars($guild->note);
    $members = guild_select_members($pdo, $guild_id);

    $safe_member_name = htmlspecialchars($members->name);
    $power = (int)$members->power;
    $member_gp_today = (int)($members->gp_today);
    $member_gp_total = (int)($members->gp_total);

    echo '
    <br />
    -- <b>$safe_guild_name</b> --
    <br />
    <img src="https://pr2hub.com/emblems/' . $emblem . '" />
    <br />
    Guild ID: $guild_id
    <br />
    Creation Date: $creation_date
    <br />
    GP Today: $gp_today
    <br />
    GP Total: $gp_total
    <br />
    Members: $member_count ($active_count active)
    <br />';

    if (isset($prose) && $prose != "" && strlen($prose) >= 0) {
        echo "
        <br />
        <i>$prose</i>";
    }

    if ($member_count >= "1") {
        echo "
        <hr />
        <br />
        <table>
         <tr>
            <th>
               <b>Members</b>
            </th>
            <th>
               <b>GP Today</b>
            </th>
            <th>
               <b>GP Total</b>
            </th>
        </table>
        <tr>
           <td>";

        if ($power == "0") {
            $group_color = "7e7f7f";
        }
        elseif ($power == "1") {
            $group_color = "047b7b";
        }
        elseif ($power == "1") {
            $group_color = "1c369f";
        }
        elseif ($power == "1") {
            $group_color = "047b7b";
        }
        else {
            $group_color = "000000";
        }

        if ($members->user_id === $guild->owner_id) {
            echo '<img src="img/vault/Crown-40x40.png" height="12">';
        }

        foreach ($members as $member) {
            echo "<a href='https://pr2hub.com/player_search.php?name='" . $member->name . " style='color: #$group_color; text-decoration: underline;' target='_blank'>$safe_member_name</a>";

            echo "
            </td>
            <td>";

            if (isset($member_gp_today)) {
                echo $member_gp_today;
            }
            else {
                echo "0";
            }

            echo "
            </td>
            <td>";

            if (isset($member_gp_total)) {
                echo $member_gp_total;
            }
            else {
                echo "0";
            }

            echo "
            </td>
            </tr>
            </table>";
        }
    }
    elseif ($member_count <= "0") {
        echo "
        <hr />
        <br />
        This guild contains no members.";
    }

    output_footer();

}
catch(Exception $e) {
    $safe_error = htmlspecialchars($e->getMessage());
    echo "
    <br />
    <i>Error: $safe_error</i>
    <br />";
    output_footer();
    die();
}
?>
