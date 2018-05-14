<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/guild_search_fns.php';
require_once QUERIES_DIR . '/guilds/guild_name_to_id.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_select_members.php';
require_once QUERIES_DIR . '/users/user_select_name_and_power.php';

$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

$guild_name = default_get('name', '');
$guild_id = (int) default_get('id', 0);
$ip = get_ip();

output_header("Guild Search");
echo '<center>';

try {
    // rate limiting
    rate_limit("gui-guild-search-" . $ip, 5, 1, "Wait a bit before searching again.");
    rate_limit("gui-guild-search-" . $ip, 30, 5, "Wait a bit before searching again.");

    // sanity check: is any data entered?
    if (is_empty($guild_name) && is_empty($guild_id, false)) {
        output_guild_search();
        output_footer();
        die();
    }

    // connect
    $pdo = pdo_connect();

    // if by name, get id
    $mode = 'id';
    if (!is_empty($guild_name) && is_empty($guild_id, false)) {
        $mode = 'name';
        $guild_id = (int) guild_name_to_id($pdo, $guild_name);
    }

    // get guild info
    $guild = guild_select($pdo, $guild_id);

    // make some variables
    $guild_id = (int) $guild->guild_id;
    $guild_name = htmlspecialchars($guild->guild_name);
    $creation_date = date('j/M/Y', strtotime($guild->creation_date));
    $active_date = date('j/M/Y', strtotime($guild->active_date));
    $emblem = $guild->emblem;
    $gp_today = (int) $guild->gp_total;
    $gp_total = (int) $guild->gp_today;
    $owner_id = (int) $guild->owner_id;
    $owner = user_select_name_and_power($pdo, $owner_id);
    $prose = htmlspecialchars($guild->note);
    $owner_name = htmlspecialchars($owner->name);
    $owner_url_name = htmlspecialchars(urlencode($owner->name));
    $owner_color = $group_colors[(int) $owner->power];
    // $active_count = (int) guild_count_active($pdo, $guild_id);
    $members = guild_select_members($pdo, $guild_id);
    $member_count = count($members);

    // check for .j instead of .jpg on the end of the emblem file name
    if (substr($emblem, -2) == '.j') {
        $emblem = str_replace('.j', '.jpg', $emblem);
    }

    // output the search box
    output_guild_search($guild_name, $guild_id, $mode);

    // display guild info
    echo "<br>-- <b>$guild_name</b> --<br>";
    if (!is_empty($prose)) {
        echo "<span style='font-size: 11px; color: slategray;'><i>$prose</i></span><br>";
    }
    echo '<br>'
        ."<img src='https://pr2hub.com/emblems/$emblem'>"
        .'<br><br>'
        ."Owner: <a href='player_search.php?name=$owner_url_name'
                    style='color: #$owner_color; text-decoration: underline;'>$owner_name
                 </a><br>"
        ."Members: $member_count <br>" // | Active: $active_count
        ."GP Today: $gp_today | GP Total: $gp_total<br>"
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
                echo '<img src="img/vault/Crown-40x40.png" height="12" title="Guild Owner"> ';
            }

            // member name column
            echo "<a href='player_search.php?name=$member_url_name'
                     style='color: #$member_color; text-decoration: underline;'>
                     $member_name
                 </a></td>";

            // gp today column
            echo '<td>'
                ."$member_gp_today"
                .'</td>';

            // gp total column
            echo '<td>'
                ."$member_gp_total"
                .'</td>';

            // end the row, move on to the next member
            echo '</tr>';
        }

    // if there are no members in the guild, show "This guild contains no members."
    } else {
        echo '<br>'
             ."This guild contains no members.";
    }

    // end the table
    echo '</table>';
} catch (Exception $e) {
    $safe_error = htmlspecialchars($e->getMessage());
    output_guild_search($guild_name, $guild_id);
    echo "<br><i>Error: $safe_error</i><br>";
} finally {
    echo '</center>';
    output_footer();
    die();
}
