<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/guild_transfers.php';

$ip = get_ip();
$guild_id = (int) default_get('guild_id', 0);
$action = default_post('action', 'lookup');

try {
    // rate limiting
    rate_limit('update-guild-'.$ip, 60, 10);
    rate_limit('update-guild-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, null, true, 3);

    // build page
    if ($action === 'lookup') {
        output_header('Update Guild', true, true);

        echo '<form name="input" action="update_guild.php" method="post">';

        $guild = guild_select($pdo, $guild_id);
        echo "guild_id: $guild->guild_id <br>---<br>";

        $guild_name = htmlspecialchars($guild->guild_name, ENT_QUOTES);
        $guild_owner = (int) $guild->owner_id;
        $guild_prose = htmlspecialchars($guild->note, ENT_QUOTES);
        $guild_id = (int) $guild->guild_id;

        echo "Guild Name: <input type='text' size='' name='guild_name' value='$guild_name'><br>";
        echo "Guild Owner: <input type='text' size='' name='owner_id' value='$guild_owner'><br>";
        echo "Prose: <textarea rows='4' name='note'>$guild_prose</textarea><br>";
        echo 'Delete Emblem? <input type="checkbox" name="delete_emblem"><br>';
        echo 'Recount Members? <input type="checkbox" name="recount_members"><br>';
        echo 'Description of Changes: <input type="text" size="100" name="guild_changes"><br>';
        echo '<input type="hidden" name="action" value="update">';
        echo "<input type='hidden' name='guild_id_submit' value='$guild_id'>";

        echo '<br/>';
        echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
        echo '</form>';

        echo '<br>';
        echo '---';
        echo '<br>';
        echo '<pre>When making changes, use the "Description of Changes" box to summarize what you did.<br>'
            .'<br>'
            .'To replace the guild owner, get the user ID of the user you want to make the owner.<br>'
            .'Then, replace the previous one in the guild owner field.<br>'
            .'<br>'
            ."NOTE: You MUST make sure that the person you're making the owner is already in the guild.</pre>";
        output_footer();
    } elseif ($action === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        // who's doing this
        $admin_ip = get_ip();

        // check to make sure the description of changes exists
        $guild_changes = default_post('guild_changes');
        if (is_empty($guild_changes)) {
            throw new Exception('The description of changes cannot be blank.');
        }

        // make some nice-looking variables out of the information in the form
        $guild_id = (int) default_post('guild_id_submit');
        $guild_name = default_post('guild_name');
        $owner_id = (int) default_post('owner_id');
        $note = default_post('note');
        $delete_emblem = default_post('delete_emblem');
        $recount_members = default_post('recount_members');

        // call guild information
        $guild = guild_select($pdo, $guild_id);

        // check if changes need to be made
        if ($guild_name === $guild->guild_name
            && $owner_id === (int) $guild->owner_id
            && $note === $guild->note
            && empty($delete_emblem)
            && empty($recount_members)
        ) {
            throw new Exception('No changes to be made.');
        }

        // log an owner transfer
        if ($guild->owner_id != $owner_id) {
            $code = 'manual-' . time();
            $old_owner = $guild->owner_id;
            $new_owner = $owner_id;

            guild_transfer_insert($pdo, $guild->guild_id, $old_owner, $new_owner, $code, $admin_ip);
            $transfer = guild_transfer_select($pdo, $code);
            guild_transfer_complete($pdo, $transfer->transfer_id, $admin_ip);
        }

        // delete a guild emblem
        $emblem = !empty($delete_emblem) ? 'default-emblem.jpg' : $guild->emblem;

        // recount members
        $member_count = !empty($recount_members) ? guild_count_members($pdo, $guild_id) : null;
            /* this can be either $guild->member_count or NULL.                           ^
               setting it to NULL doesn't add the sql line to update the member count
               setting it to $guild->member_count tells the db to update the count to what it already is */

        // do it
        guild_update($pdo, $guild_id, $guild_name, $emblem, $note, $owner_id, $member_count);

        // admin log
        $str = "$admin->name updated guild #$guild->guild_id from $admin_ip";
        if ($guild_name !== $guild->guild_name
            || $note !== $guild->note
            || $emblem !== $guild->emblem
            || $owner_id !== (int) $guild->owner_id
            || !empty($recount_members)
        ) {
            $changes = false;
            $str .= ' {';
            if ($guild_name !== $guild->guild_name) {
                $str .= "old_name: $guild->guild_name, new_name: $guild_name";
                $changes = true;
            }
            if ($note !== $guild->note) {
                $str = $str . ($changes === true ? '; ' : '');
                $str .= "old_note: $guild->note, new_note: $note";
                $changes = true;
            }
            if ($emblem !== $guild->emblem) {
                $str = $str . ($changes === true ? '; ' : '');
                $str .= "old_emblem: $guild->emblem, new_emblem: $emblem";
                $changes = true;
            }
            if ($owner_id !== (int) $guild->owner_id) {
                $str = $str . ($changes === true ? '; ' : '');
                $str .= "old_owner: $guild->owner_id, new_owner: $owner_id";
                $changes = true;
            }
            if (!empty($recount_members)) {
                $str = $str . ($changes === true ? '; ' : '');
                $str .= "old_member_count: $guild->member_count, new_member_count: $member_count";
                $changes = true;
            }
            $str .= '}';
        }
        admin_action_insert($pdo, $admin->user_id, $str, 'guild-update', $admin_ip);

        // redirect
        header("Location: guild_deep_info.php?guild_id=" . urlencode($guild->guild_id));
        die();
    } else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    output_header("Error");
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
}
