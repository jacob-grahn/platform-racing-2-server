<?php

function run_update_cycle($pdo)
{
    output('run update cycle');
    //--- gather data to send to active servers
    $send = new stdClass();
    $send->artifact = artifact_location_select($pdo);
    $send->recent_pms = get_recent_pms($pdo);
    $send->recent_bans = bans_select_recent($pdo);
    $send->campaign = levels_select_campaign($pdo);
    $send_str = json_encode($send);

    //--- send the data
    $server_list = servers_select($pdo);
    $servers = poll_servers($server_list, 'update_cycle`' . $send_str);

    //--- process replies
    foreach ($servers as $server) {
        if ($server->result != false && $server->result != null) {
            $happy_hour = (int)$server->result->happy_hour;
            output('server is up');
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
            output('server is down: ' . json_encode($server));
            server_update_status($pdo, $server->server_id, 'down', 0, 0);
        }
    }
}



function write_server_status($pdo)
{
    $servers = servers_select($pdo);
    $displays = array();
    foreach ($servers as $server) {
        $display = new stdClass();
        output('server id ' . $server->server_name);
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
    }

    $save = new stdClass();
    $save->servers = $displays;
    $display_str = json_encode($save);

    output('output display str');
    output($display_str);

    file_put_contents(__DIR__ . '/../www/files/server_status_2.txt', $display_str);
}



function get_recent_pms($pdo)
{
    $file = __DIR__ . '/../cron/last-pm.txt';
    //--- get the last message id that a notifacation was sent for
    $last_message_id = file_get_contents($file);
    if (!isset($last_message_id)) {
        $last_message_id = 0;
    }

    //--- select the messages
    output("last_message_id: $last_message_id");
    $messages = messages_select_recent($pdo, $last_message_id);

    if (count($messages) > 0) {
        $last_message = $messages[count($messages) - 1];
        $last_message_id = $last_message->message_id;
    }

    //--- save the message id for next time
    file_put_contents($file, $last_message_id);

    //--- done
    return $messages;
}



function save_plays($pdo, $plays)
{
    foreach ($plays as $course => $plays) {
        level_increment_play_count($pdo, $course, $plays);
    }
}



function save_gp($pdo, $server_id, $gp_array)
{
    foreach ($gp_array as $user_id => $gp) {
        $user = user_select($pdo, $user_id);
        $guild_id = $user->guild;
        if ($guild_id > 0 && $server_id == $user->server_id) {
            gp_increment($pdo, $user_id, $guild_id, $gp);
            guild_increment_gp($pdo, $guild_id, $gp);
        }
    }
}



function output($str)
{
    echo $str . "\n";
}
