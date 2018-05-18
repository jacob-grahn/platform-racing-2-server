<?php

function promote_server_mod($name, $owner, $promoted)
{
    global $guild_owner;

    // safety first
    $safe_name = htmlspecialchars($name);

    // if the user doesn't own the server, kill the function (2nd line of defense)
    if ($owner->group < 3 ||
        $owner->server_owner == false ||
        $owner->user_id != $guild_owner ||
        $owner->user_id == 4291976
    ) {
        $owner->write("message`Error: You lack the power to promote $safe_name to a server moderator.");
        return false;
    }

    // sanity check: is the user online?
    if (!isset($promoted)) {
        $owner->write("message`Error: Could not find a user with the name \"$safe_name\" on this server.");
        return false;
    }

    // sanity check: is the user being promoted already a staff member?
    if ($promoted->group >= 2 && $promoted->temp_mod == false) {
        $owner->write("message`Error: I'm not sure what would happen if you ".
            "promoted a staff member to a server moderator, but it would ".
            "probably make the world explode.");
        return false;
    }

    // sanity check: is the person being promoted a guest? (this should never happen on a private server...)
    if ($promoted->group == 0) {
        $owner->write("message`Error: Guests can't be promoted to server moderators.");
        return false;
    }

    // if the person being promoted is an admin, kill the function
    if ($promoted->group == 3) {
        $owner->write("message`Error: I'm not sure what would happen if you ".
            "promoted an admin to a moderator, but it would probably make ".
            "the world explode.");
        return false;
    }

    // if they're the server owner and have gotten this far, promote the user to a server mod
    if ($owner->server_owner == true) {
        $promoted->becomeTempMod();
        $owner->write("message`$safe_name has been promoted to a server moderator! ".
            "They'll remain a moderator until you type /demod *their name* or until they log out.");
        if (isset($owner->chat_room)) {
            $owner_url = userify($owner, $owner->name);
            $promoted_url = userify($promoted, $promoted->name);
            
            $owner->chat_room->sendChat("systemChat`$owner_url has promoted ".
                "$promoted_url to a server moderator! Your private peace-keeping is ".
                "greatly appreciated! You'll have your mod powers until you log ".
                "out or are demoted.");
        }
        return true;
    }

    return false;
}

function demote_server_mod($name, $owner, $demoted)
{
    global $guild_owner;

    // safety first
    $safe_name = htmlspecialchars($name);

    // sanity check: does the user own the server?
    if ($owner->group < 3 || $owner->server_owner == false || $owner->user_id != $guild_owner) {
        $owner->write("message`Error: You lack the power to demote $safe_name.");
        return false;
    }

    // sanity check: is the server owner trying to demote themselves? lol
    if ($demoted->user_id == $guild_owner) {
        $owner->write("message`Error: You can't demote yourself on your own private server, silly!");
        return false;
    }

    // sanity check: is the user online? are they a temp?
    if (!isset($demoted) || $demoted->temp_mod == false) {
        $owner->write("message`Error: Could not find a server moderator with the name \"$safe_name\" on this server.");
        return false;
    }

    // if they're the server owner and have gotten this far, demote the user
    if ($owner->server_owner == true) {
        $demoted->group = 1;
        $demoted->write('setGroup`1');
        $demoted->temp_mod = false;
        $owner->write("message`$safe_name has been demoted.");
        return true;
    }

    return false;
}
