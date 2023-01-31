<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/level_backups.php';

// variables
$ip = get_ip();
$level_id = (int) default_get('id', 0);
$action = default_post('action', 'lookup');

try {
    // rate limiting
    rate_limit('admin-update-level-'.$ip, 60, 10);
    rate_limit('admin-update-level-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, null, true, 3);

    // build page
    if ($action === 'lookup') {
        output_header('Update PR2 Level', true, true);
    
        echo '<form name="input" action="update_level.php" method="post">';

        $level = level_select($pdo, $level_id);

        $level_id = (int) $level->level_id;
        $owner_id = (int) $level->user_id;
        $title = htmlspecialchars($level->title, ENT_QUOTES);
        $live = check_value($level->live, 1, "checked='checked'", '');
        $restricted = check_value($level->restricted, 1, "checked='checked'", '');
        $note = htmlspecialchars($level->note, ENT_QUOTES);

        echo "level_id: $level_id";
        echo "<br>---<br>";
        echo "Owner ID: <input type='text' name='owner_id' value='$owner_id'><br>";
        echo "Title: <input type='text' name='title' value='$title'>";
        echo "<label><input type='checkbox' name='live' $live /> Published</label> | ";
        echo "<label><input type='checkbox' name='restricted' $restricted /> Restricted</label><br>";
        echo "Note: <textarea rows='4' name='note'>$note</textarea><br>";
        echo 'Description of Changes: <input type="text" size="100" name="level_changes"><br>';
        echo '<input type="hidden" name="action" value="update">';
        echo "<input type='hidden' name='post_id' value='$level_id'>";

        echo '<br/>';
        echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
        echo '</form>';

        echo '<br>';
        echo '---';
        echo '<br>';
        echo '<pre>When making changes, use the "Description of Changes" box to summarize what you did.<br>'
            .'<br>'
            .'To replace the level owner ID, get the user ID of the user you want to make the owner.<br>'
            .'Then, replace the previous one in the guild owner field.<br>';
    } elseif ($action === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }

        // make some nice variables
        $admin_ip = get_ip();
        $level_id = (int) default_post('post_id');
        $owner_id = (int) default_post('owner_id');
        $title = default_post('title');
        $live = (int) !empty(default_post('live'));
        $restricted = (int) !empty(default_post('restricted'));
        $note = default_post('note');
        $level_changes = default_post('level_changes');

        // sanity checks
        if (empty($level_id) || empty($owner_id) || is_empty($title)) { // valid level/owner IDs and title?
            throw new Exception('Some data is missing. Make sure a valid level/owner ID and title were submitted.');
        } if (is_empty($level_changes)) { // description of changes?
            throw new Exception('You must enter a description of your changes.');
        }

        // call level information
        $level = level_select($pdo, $level_id);

        // specify what to change
        $update_owner = $level->user_id != $owner_id;
        $update_visibility = $level->live != $live || $level->restricted != $restricted;
        $update_info = $level->title != $title || $level->note != $note;

        // if there's nothing to change, no need to query the database any further
        if (!$update_owner && !$update_visibility && !$update_info) {
            throw new Exception('No changes to be made.');
        }

        // make sure the new owner exists
        if ($update_owner && id_to_name($pdo, $owner_id, true) === false) {
            throw new Exception('Could not find a user with that ID.');
        }

        // check if a level with this title exists on target account
        $level_conflict = level_select_by_title($pdo, $owner_id, $title);
        if (!empty($level_conflict) && $level_id != $level_conflict->level_id) {
            throw new Exception('A level already exists with this title on the target account. Please choose a different title.');
        }

        // back up the level (if none exists)
        $latest_backup = level_backups_select_latest_by_level($pdo, $level_id);
        if (empty($latest_backup) || $latest_backup->version < $level->version) {
            $s3 = s3_connect();
            backup_level(
                $pdo,
                $s3,
                $level->user_id,
                $level_id,
                $level->version,
                $level->title,
                $level->live,
                $level->rating,
                $level->votes,
                $level->note,
                $level->min_level,
                $level->song,
                $level->play_count,
                $level->pass,
                $level->type,
                $level->bad_hats
            );
        }

        // move backups to new owner
        if ($update_owner) {
            level_backups_switch_owner($pdo, $level_id, $owner_id);
        }

        // update the level
        admin_level_update($pdo, $level_id, $owner_id, $title, $note, $live, $restricted);

        // log the action in the admin log
        $obj = new stdClass();
        $obj->old = new stdClass();
        $obj->new = new stdClass();
        if ($update_owner) {
            [$obj->old->owner, $obj->new->owner] = [(int) $level->user_id, $owner_id];
        }
        if ($level->title != $title) {
            [$obj->old->title, $obj->new->title] = [$level->title, $title];
        }
        if ($level->note != $note) {
            [$obj->old->note, $obj->new->note] = [$level->note, $note];
        }
        if ($update_visibility) {
            $obj->old->visibility = new stdClass();
            $obj->new->visibility = new stdClass();
            if ($level->live != $live) {
                [$obj->old->visibility->live, $obj->new->visibility->live] = [(int) $level->live, $live];
            }
            if ($level->restricted != $restricted) {
                $obj->old->visibility->restricted = (int) $level->restricted;
                $obj->new->visibility->restricted = $restricted;
            }
        }
        $obj->changes = $level_changes;
        $change_data = json_encode($obj, JSON_UNESCAPED_UNICODE);
        $msg = "$admin->name updated level #$level_id from $admin_ip. $change_data";
        admin_action_insert($pdo, $admin->user_id, $msg, 'level-update', $admin_ip);

        header("Location: level_deep_info.php?level_id=$level_id");
        die();
    } else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    output_header("Error");
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
