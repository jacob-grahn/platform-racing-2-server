<?php

function promote_server_mod($name, $owner, $promoted)
{
    global $guild_owner;

    // safety first
    $safe_name = htmlspecialchars($name, ENT_QUOTES);

    // if the user doesn't own the server, kill the function
    if ($owner->group < 3
        || $owner->server_owner === false
        || $owner->user_id !== $guild_owner
        || $owner->user_id === FRED
    ) {
        $owner->write("message`Error: You lack the power to promote $safe_name to a server moderator.");
        return false;
    }

    // check if the target is online
    if (!isset($promoted)) {
        $owner->write("message`Error: Could not find a user with the name \"$safe_name\" on this server.");
        return false;
    }

    // check if the user being promoted is already a staff member
    if ($promoted->group >= 2 && $promoted->temp_mod === false && $promoted->trial_mod === false) {
        $err = 'I\'m not sure what would happen if you promoted a staff member to a server moderator, '.
            'but it would probably make the world explode.';
        $owner->write("message`Error: $err");
        return false;
    }

    // check if the player being promoted is a guest (this should never happen on a private server...)
    if ((int) $promoted->group === 0) {
        $owner->write("message`Error: Guests can't be promoted to server moderators.");
        return false;
    }

    // if the person being promoted is an admin, kill the function
    if ((int) $promoted->group === 3) {
        $err = 'I\'m not sure what would happen if you promoted an admin to a moderator, '.
            'but it would probably make the world explode.';
        $owner->write("message`Error: $err");
        return false;
    }

    // if they're the server owner and have gotten this far, promote the user to a server mod
    if ($owner->server_owner === true) {
        $promoted->becomeTempMod();
        $msg = "$safe_name has been promoted to a server moderator! ".
            'They\'ll remain a moderator until you type /demod *their name* or until they log out.';
        $owner->write("message`$msg");
        if (isset($owner->chat_room)) {
            $owner_url = userify($owner, $owner->name);
            $promoted_url = userify($promoted, $promoted->name);
            $msg = "$owner_url has promoted $promoted_url to a server moderator! "
                ."Your private peace-keeping is greatly appreciated! "
                ."You'll have your mod powers until you log out or are demoted.";
            $owner->chat_room->sendChat("systemChat`$msg");
        }
        return true;
    }

    return false;
}

function demote_server_mod($name, $owner, $demoted)
{
    global $guild_owner;

    // safety first
    $safe_name = htmlspecialchars($name, ENT_QUOTES);

    // sanity check: does the user own the server?
    if ($owner->group < 3 || $owner->server_owner === false || $owner->user_id !== $guild_owner) {
        $owner->write("message`Error: You lack the power to demote $safe_name.");
        return false;
    }

    // sanity check: is the server owner trying to demote themselves? lol
    if ($demoted->user_id === $guild_owner) {
        $owner->write("message`Error: You can't demote yourself on your own private server, silly!");
        return false;
    }

    // sanity check: is the user online? are they a temp?
    if (!isset($demoted) || $demoted->temp_mod === false) {
        $owner->write("message`Error: Could not find a server moderator with the name \"$safe_name\" on this server.");
        return false;
    }

    // if they're the server owner and have gotten this far, demote the user
    if ($owner->server_owner === true) {
        $demoted->group = 1;
        $demoted->temp_mod = false;
        $demoted->write('demoteMod`');
        $owner->write("message`$safe_name has been demoted.");
        return true;
    }

    return false;
}
