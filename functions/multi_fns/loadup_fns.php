<?php


function begin_loadup($server_id)
{
    // apply server information to global variables
    $server = db_op('server_select', array($server_id));
    configure_server($server);

    // set campaign
    $campaign = db_op('campaign_select');
    set_campaign($campaign);

    // activate vault items
    $perks = db_op('vault_purchases_select_active');
    activate_perks($perks);

    // place the artifact
    $artifact_location = db_op('artifact_location_select');
    place_artifact($artifact_location);

    // start a happy hour, but only if this server is Carina
    if ($server_id === 2 && \pr2\multi\HappyHour::isActive() === false) {
        \pr2\multi\HappyHour::activate();
    }
}


function configure_server($server)
{
    global $port, $server_name, $server_expire_time, $guild_id, $guild_owner, $is_ps;

    // server information
    $port = (int) $server->port;
    $server_name = $server->server_name;
    $server_expire_time = (int) $server->expire_time;
    $guild_id = (int) $server->guild_id;
    $is_ps = $guild_id !== 0 && $guild_id !== 183 && $guild_id !== 205;

    // no prizes on tournament
    \pr2\multi\PR2SocketServer::$tournament = (bool) (int) $server->tournament;
    \pr2\multi\PR2SocketServer::$no_prizes = \pr2\multi\PR2SocketServer::$tournament;

    // restore happy hour if there was one when the server restarted
    // one is always activated on startup on carina, so excl. if time remaining doesn't exceed 3600
    if ((int) $server->server_id !== 2 || $server->happy_hour > 3600) {
        \pr2\multi\HappyHour::activate($server->happy_hour);
    }

    // set server owner
    if ($guild_id !== 0) {
        $guild = db_op('guild_select', array($guild_id));
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
        $expire_time = $perk->start_time + ($perk->quantity * 3600);
        if ($expire_time <= time()) {
            continue;
        }
        output("Activating: $perk->slug | User: $perk->user_id | Guild: $perk->guild_id | Quantity: $perk->quantity");
        start_perk($perk->slug, $perk->user_id, $perk->guild_id, $expire_time, $perk->start_time);
    }
}
