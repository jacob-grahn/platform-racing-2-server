<?php


function begin_loadup($server_id)
{
    global $pdo, $server_id;

    $server = server_select($pdo, $server_id);
    $campaign = campaign_select($pdo);
    $perks = purchases_select_recent($pdo);
    $artifact = artifact_location_select($pdo);

    set_server($pdo, $server);
    set_campaign($campaign);
    set_perks($perks);
    place_artifact($artifact);
    if ($server_id == 2) {
        \pr2\multi\HappyHour::activate();
    }
}



function set_server($pdo, $server)
{
    global $port, $server_name, $uptime, $server_expire_time, $guild_id, $guild_owner, $key;
    $port = $server->port;
    $server_name = $server->server_name;
    $datetime = new DateTime();
    $uptime = $datetime->format('Y-m-d H:i:s P');
    $server_expire_time = $server->expire_date;
    $guild_id = $server->guild_id;
    $guild_owner = 0;
    $key = $server->salt;
    \pr2\multi\PR2SocketServer::$tournament = $server->tournament;
    if (\pr2\multi\PR2SocketServer::$tournament) {
        \pr2\multi\PR2SocketServer::$no_prizes = true;
    }

    if ($guild_id != 0) {
        $guild = guild_select($pdo, $guild_id);
        $guild_owner = $guild->owner_id;
    } else {
        $guild_owner = FRED;
    }
}



function set_campaign($campaign_levels)
{
    global $campaign_array;
    $campaign_array = array();
    foreach ($campaign_levels as $level) {
        $campaign_array[$level->level_id] = $level;
    }
}



function set_perks($perks)
{
    foreach ($perks as $perk) {
        $slug = $perk->product;
        $a = [\pr2\multi\Perks::GUILD_FRED, \pr2\multi\Perks::GUILD_GHOST];
        if (array_search($slug, $a) !== false) {
            output("activating perk $slug for user $perk->user_id and guild $perk->guild_id");
            start_perk($slug, $perk->user_id, $perk->guild_id);
        }
        if ($slug == 'happy-hour') {
            \pr2\multi\HappyHour::activate();
        }
    }
}
