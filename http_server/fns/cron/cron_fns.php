<?php


function run_update_cycle($pdo)
{
    output('Running update cycle...');
    
    // gather data to send to active servers
    $send = new stdClass();
    $send->artifact = artifact_location_select($pdo);
    $send->recent_pms = get_recent_pms($pdo);
    $send->recent_bans = bans_select_recent($pdo);
    $send->campaign = levels_select_campaign($pdo);
    $send_str = json_encode($send);

    // send the data
    $server_list = servers_select($pdo);
    $servers = poll_servers($server_list, 'update_cycle`' . $send_str);

    // process replies
    foreach ($servers as $server) {
        if ($server->result != false && $server->result != null) {
            $happy_hour = (int)$server->result->happy_hour;
            output("$server->server_name (ID #$server->server_id) is up.");
            save_plays($pdo, $server->result->plays);
            save_gp($pdo, $server->server_id, $server->result->gp);
            server_update_status(
                $pdo,
                $server->server_id,
                $server->result->status,
                $server->result->population,
                $happy_hour
            );
        } else {
            $server_str = json_encode($server);
            output("$server->server_name is down. Data: $server_str");
            server_update_status($pdo, $server->server_id, 'down', 0, 0);
        }
    }
    output('Update cycle complete.');
}


function write_server_status($pdo)
{
    $servers = servers_select($pdo);
    $displays = array();
    foreach ($servers as $server) {
        $display = new stdClass();
        output("Writing status for $server->server_name (ID #$server->server_id)...");
        $display->server_id = $server->server_id;
        $display->server_name = preg_replace("/[^A-Za-z0-9 ]/", '', $server->server_name);
        $display->address = $server->address;
        $display->port = $server->port;
        $display->population = $server->population;
        $display->status = $server->status;
        $display->guild_id = $server->guild_id;
        $display->tournament = $server->tournament;
        $display->happy_hour = $server->happy_hour;
        $displays[] = $display;
        output("Status written for $server->server_name (ID #$server->server_id).");
    }

    $save = new stdClass();
    $save->servers = $displays;
    $display_str = json_encode($save);

    output('Outputting server status:');
    output($display_str);

    file_put_contents(WWW_ROOT . '/files/server_status_2.txt', $display_str);
    
    output('Server status output successful.');
}


function get_recent_pms($pdo)
{
    $file = COMMON_DIR . '/cron/last-pm.txt';
    
    // get the last message id that a notifacation was sent for
    $last_message_id = file_get_contents($file);
    if (!isset($last_message_id)) {
        $last_message_id = 0;
    }

    // select the messages
    output("Last Message ID: $last_message_id");
    $messages = messages_select_recent($pdo, $last_message_id);

    if (count($messages) > 0) {
        $last_message = $messages[count($messages) - 1];
        $last_message_id = $last_message->message_id;
    }

    // save the message id for next time
    file_put_contents($file, $last_message_id);
    
    output("Wrote last message ID to $file.");

    // done
    return $messages;
}


function save_plays($pdo, $plays)
{
    if (!empty($plays)) {
        foreach ($plays as $course => $plays) {
            level_increment_play_count($pdo, $course, $plays);
        }
    }
}


function save_gp($pdo, $server_id, $gp_array)
{
    if (!empty($gp_array)) {
        foreach ($gp_array as $user_id => $gp) {
            $user = user_select($pdo, $user_id);
            $guild_id = $user->guild;
            if ($guild_id > 0 && $server_id == $user->server_id) {
                gp_increment($pdo, $user_id, $guild_id, $gp);
                guild_increment_gp($pdo, $guild_id, $gp);
            }
        }
    }
}


function update_artifact($pdo)
{
    // collect data
    $artifact = artifact_location_select($pdo);
    $level_id = $artifact->level_id;
    $updated_time = strtotime($artifact->updated_time);
    $first_finder = $artifact->first_finder;
    
    $level = level_select($pdo, $level_id);
    $title = $level->title;
    $user_id = $level->user_id;
    
    $user = user_select($pdo, $user_id);
    $user_name = $user->name;
    
    if ($first_finder != 0) {
        $finder = user_select($pdo, $first_finder);
        $finder_name = $finder->name;
    } else {
        $finder_name = '';
    }
    
    
    // form the base string we'll be creating
    $str = "$title by $user_name";
    $len = strlen($str);
    
    
    // figure out how much of the string to reveal
    $elapsed = time() - $updated_time;
    $perc = $elapsed / 259200; // 3 days
    if ($perc > 1) {
        $perc = 1;
    }
    $hide_perc = 1 - $perc;
    $disp_perc = $hide_perc * 100;
    $hide_characters = round($len * $hide_perc);
    output("Hidden Percent: $disp_perc%");
    output("Hidden Chars: $hide_characters");
    output("String Length: $len");
    output("Finder Name: $finder_name");
    
    
    // generate random
    \pr2\http\PseudoRandom::seed(112);
    
    
    // replace a percentage of characters with underscores
    $arr = str_split($str);
    $loops = 0;
    while ($hide_characters > 0) {
        $index = \pr2\http\PseudoRandom::num(0, $len-1);
    
        while ($arr[$index] == '_') {
            $index++;
            if ($index >= $len) {
                $index = 0;
            }
    
            $loops++;
            if ($loops > 100) {
                output('Infinite loop triggered, breaking...');
                break;
            }
        }
        $arr[ $index ] = '_';
        $hide_characters--;
    }
    
    
    // tell it to the world
    $r = new stdClass();
    $r->hint = join('', $arr);
    $r->finder_name = $finder_name;
    $r->updated_time = $updated_time;
    $r_str = json_encode($r);
    
    file_put_contents(WWW_ROOT . '/files/artifact_hint.txt', $r_str);
    output($r->hint);
}


function failover_servers($pdo)
{
    // list servers
    $servers = servers_select($pdo);
    $addresses = array('45.76.24.255'); // todo: this should be in the db

    // restart if down
    foreach ($servers as $server) {
        if ($server->status == 'down') {
            $fallback_address = $addresses[array_rand($addresses)];
            server_update_address($pdo, $server->server_id, $fallback_address);
        }
    }

    // tell the world
    output('The correct address of all active servers has been ensured.');
}


function servers_restart_all($pdo)
{
    // tell the command line
    $time = date('r');
    output("Mandatory server reboot CRON starting at $time...");

    // grab active servers
    $servers = servers_select($pdo);
    
    // shut down all active servers
    foreach ($servers as $server) {
        output("Shutting down $server->server_name (ID: #$server->server_id)...");
        try {
            $reply = talk_to_server($server->address, $server->port, $server->salt, 'shut_down`', true);
            output("Reply: $reply");
            output("$server->server_name (ID #$server->server_id) shut down successful.");
        } catch (Exception $e) {
            output($e->getMessage());
        }
    }

    // tell the command line
    output('Mandatory reboot finished.');
}


function delete_old_accounts($pdo)
{
    set_time_limit(0);
    $start_time = time();

    // get data
    $users = users_select_old($pdo);
    
    // tell the world
    $num_users = number_format(count($users));
    output("$num_users accounts meet the time criteria for deletion.");

    // count
    $spared = 0;
    $deleted = 0;
    
    // delete or spare
    foreach ($users as $row) {
        $user_id = $row->user_id;
        $rank = $row->rank;

        $play_count = user_select_level_plays($pdo, $user_id);

        $str = "$user_id has $play_count level plays and is rank $rank.";
        if ($play_count > 100 || $rank > 15) {
            output("$str Spared!");
            $spared++;
        } else {
            output("$str DELETING...");
            user_delete($pdo, $user_id);
            output("$user_id was successfully deleted.");
            $deleted++;
        }
    }
    
    // tell the world
    $total_secs = time() - $start_time;
    $time = format_duration($total_secs);
    output("Old account deletion completed. Stats:\n".
        "Spared: $spared / $num_users\n".
        "Deleted: $deleted / $num_users\n".
        "Time: $time ($t_elapsed seconds)"
    );
}
