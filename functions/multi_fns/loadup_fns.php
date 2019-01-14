<?php


function begin_loadup($pdo, $server_id)
{
    // apply server information to global variables
    $server = server_select($pdo, $server_id);
    configure_server($pdo, $server);

    // set campaign
    $campaign = campaign_select($pdo);
    set_campaign($campaign);

    // activate vault items
    $perks = purchases_select_recent($pdo);
    activate_perks($perks);

    // place the artifact
    $artifact_location = artifact_location_select($pdo);
    place_artifact($artifact_location);

    // start a happy hour, but only if this server is Carina
    if ($server_id === 2) {
        \pr2\multi\HappyHour::activate();
    }
}


function configure_server($pdo, $server)
{
    global $port, $server_name, $server_expire_time, $guild_id, $guild_owner;

    // server information
    $port = (int) $server->port;
    $server_name = $server->server_name;
    $server_expire_time = $server->expire_date;
    $guild_id = (int) $server->guild_id;

    // no prizes on tournament
    \pr2\multi\PR2SocketServer::$tournament = (bool) (int) $server->tournament;
    \pr2\multi\PR2SocketServer::$no_prizes = \pr2\multi\PR2SocketServer::$tournament;

    // set server owner
    if ($guild_id !== 0) {
        $guild = guild_select($pdo, $guild_id);
        $guild_owner = (int) @$guild->owner_id;
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


function activate_perks($perks)
{
    foreach ($perks as $perk) {
        $slug = $perk->product;
        $a = ['guild-fred', 'guild-ghost', 'guild-artifact'];
        if (array_search($slug, $a) !== false) {
            output("Activating perk $slug for user $perk->user_id and guild $perk->guild_id...");
            start_perk($slug, $perk->user_id, $perk->guild_id);
        }
        if ($slug === 'happy-hour') {
            \pr2\multi\HappyHour::activate();
        }
    }
}
