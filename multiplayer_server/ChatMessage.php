<?php

namespace pr2\multi;

class ChatMessage
{

    public $from_id;
    public $message;
    private $player;
    private $room_type;
    private $room;

    public function __construct($player, $chat_message)
    {
        $this->player = $player;
        $this->from_id = $this->player->user_id;
        $this->message = $chat_message;

        // sanity check: is the message more than 150 characters?
        if (strlen($this->message) > 150) {
            $this->message = substr($this->message, 0, 150);
        }

        // find what room the player is in
        if (isset($this->player->chat_room) && !isset($this->player->game_room)) {
            $this->room_type = 'c'; // c for chat
            $this->room = $this->player->chat_room;
        } elseif (!isset($this->player->chat_room) && isset($this->player->game_room)) {
            $this->room_type = 'g'; // g for game
            $this->room = $this->player->game_room;
        } elseif (isset($this->player->chat_room) && isset($this->player->game_room)) {
            $this->room_type = 'b'; // b for both -- should never happen
            $this->room = null;
        } else {
            $this->room_type = 'n'; // n for none -- should also never happen
            $this->room = null;
        }

        $this->handleEmotes();
        if (strpos($this->message, '/') === 0 && isset($this->room)) {
            $this->handleCommand();
        } elseif (isset($this->room)) {
            $this->handleMessage();
        } else {
            if ($this->room_type === 'b') {
                $this->write('message`Error: You can\'t be in two places at once!'); // should never happen
            } elseif ($this->room_type === 'n') {
                $this->write('message`Error: You don\'t seem to be in a valid chatroom.'); // should also never happen
            } else {
                $this->write(
                    'message`Error: Could not determine what chatroom you\'re in. '.
                    'Try rejoining the chatroom and sending your message again. '.
                    'If this error persists, contact a member of the PR2 Staff Team.'
                ); // most certainly won't happen without any funny business
            }
        }
    }


    // special text emotes
    private function handleEmotes()
    {
        $this->message = str_ireplace(':shrug:', '‾\_(ツ)_/‾', $this->message);
        $this->message = str_ireplace(':lenny:', '( ͡° ͜ʖ ͡°)', $this->message);
        $this->message = str_ireplace([':yay:', ':woohoo:', ':wow:'], '╰(ᴖ◡ᴖ)╯', $this->message);
        $this->message = str_ireplace([':hi:', ':hello:', ':hey:'], 'ー( ◉▽◉ )ﾉ', $this->message);
        $this->message = str_ireplace([':thinking:', ':think:', ':what:', ':hmm:'], '🤔', $this->message);
        $this->message = str_ireplace([':lol:', ':laugh:', ':lmao:', ':joy:'], '😂', $this->message);
        $this->message = str_ireplace([':hooray:', ':tada:', ':party:'], '🎉', $this->message);
        $this->message = str_ireplace([':fred:', ':cactus:'], '🌵', $this->message);
    }


    // handle a chat command
    private function handleCommand()
    {
        global $guild_id, $guild_owner;

        $t_arr = ['t', 'tournament'];
        $effect_arr = ['b', 'u', 'i', 'li'];
        $emotes_arr = ['emotes', 'emoticons', 'emojis', 'smilies', 'smiles'];
        $help_arr = ['help', 'commands', '?'];
        $msg = strtolower($this->message);
        $msg_trim = strtolower(trim($msg, '/'));
        $handled = false;

        // for mods and up (including server owners)
        if ($this->isMod() === true) {
            if (in_array($msg_trim, $effect_arr)) {
                $this->commandModTextEffect(); // activate html text effect in chat (b, i, u, li)
                $handled = true;
            } elseif (strpos($msg, '/a ') === 0) {
                $this->commandModAnnouncement(); // make an announcement
                $handled = true;
            } elseif (strpos($msg, '/give ') === 0) {
                $this->commandModFakeGive(); // "give"
                $handled = true;
            } elseif (($msg === '/clear' || $msg === '/cls')) {
                $this->commandModClearChatroom(); // clear the chatroom
                $handled = true;
            } elseif (strpos($msg, '/kick ') === 0) {
                $this->commandModKick(); // kick for mods
                $handled = true;
            } elseif (strpos($msg, '/unkick ') === 0) {
                $this->commandModUnKick(); // unkick for mods
                $handled = true;
            } elseif (strpos($msg, '/unmute ') === 0 || strpos($msg, '/unwarn ') === 0) {
                $this->commandModUnMute(); // unmute for mods
                $handled = true;
            } elseif ((strpos($msg, '/dc ') === 0 || strpos($msg, '/disconnect ') === 0)) {
                $this->commandModDisconnect(); // dc for mods
                $handled = true;
            } elseif (strpos($msg, '/priors ') === 0 && $this->isServerOwner() === false) {
                $this->commandModViewPriors(); // view a user's priors for mods (NOT server owners)
                $handled = true;
            }
        }

        // for admins (not server owners unless on the PR2 Staff server)
        if ($this->isAdmin() === true || ($this->isServerOwner() === true && $guild_id === 183)) {
            if ($msg === '/debug' || strpos($msg, '/debug ') === 0) {
                $this->commandAdminDebug(); // debug info for admins
                $handled = true;
            } elseif (strpos($msg, '/promote ') === 0) {
                $this->commandAdminFakePromote(); // "promote"
                $handled = true;
            } elseif ($msg === '/restart_server') {
                $this->commandAdminRestartServer(); // server restart for admins
                $handled = true;
            }
        }

        // for server owners
        if ($this->isServerOwner() === true) {
            if (($msg === '/mod' || strpos($msg, '/mod ') === 0) && $guild_owner !== FRED) {
                $this->commandSOPromoteMod(); // promote server mod
                $handled = true;
            } elseif ($msg === '/timeleft') {
                $this->commandSOTimeLeft(); // time left in a private server
                $handled = true;
            }
        }

        // for everyone
        if ($msg === '/be_awesome' || $msg === '/beawesome') {
            $this->commandBeAwesome(); // be awesome
            $handled = true;
        } elseif (($msg === '/hh' || strpos($msg, '/hh ') === 0)) {
            $this->commandHappyHour(); // happy hour-related functions (start/stop, status)
            $handled = true;
        } elseif ($msg === '/pop' || $msg === '/population') {
            $this->commandPopulation(); // get current server population
            $handled = true;
        } elseif (strpos($msg, '/t ') === 0 || strpos($msg, '/tournament ') === 0 || in_array($msg_trim, $t_arr)) {
            $this->commandTournament(); // tournament-related commands (start/stop, status)
            $handled = true;
        } elseif (in_array($msg_trim, $emotes_arr)) {
            $this->commandViewEmotes(); // view emotes
            $handled = true;
        } elseif ($msg === '/guides' || $msg === '/guide') {
            $this->commandViewGuides(); // view guides
            $handled = true;
        } elseif (in_array($msg_trim, $help_arr) || $msg === '/') {
            $this->commandViewHelp(); // view help
            $handled = true;
        } elseif ($msg === '/rules') {
            $this->commandViewRules(); // view rules
            $handled = true;
        } elseif ($msg === '/here') {
            $this->commandWhoIsHere(); // who's in the chatroom
            $handled = true;
        } else {
            if ($handled === false) {
                $this->handleMessage(); // handle as a message if no commands fit
            }
        }
    }


    // handle a chat message
    private function handleMessage()
    {
        // make sure they're allowed to send a message
        if ($this->player->group <= 0 || $this->player->guest === true) {
            $this->write('systemChat`Sorries, guests can\'t send chat messages.'); // guest check
        } elseif ($this->player->active_rank < 3 && $this->player->group < 2) {
            $this->write('systemChat`Sorries, you must be rank 3 or above to chat.'); // rank 3 check
        } elseif (Mutes::isMuted($this->player->name) === true) {
            $cb_secs = (int) Mutes::remainingTime($this->player->name);
            $ret = "You have been temporarily muted from the chat. The mute will be lifted in $cb_secs seconds.";
            $this->write("systemChat`$ret"); // muted check (warnings, auto-warn, manual mute duration)
        } elseif ($this->player->getChatCount() > 6 && ($this->player->group < 2 || $this->isTempMod() === true)) {
            Mutes::add($this->player->name, 60);
            $this->write('systemChat`Slow down a bit, yo.'); // spamming check
        } elseif (strpos($this->player->name, '`') !== false || strpos($this->message, '`') !== false) {
            $this->write('message`Error: Illegal character detected.'); // illegal character in username/message check
        } else {
            $name = $this->player->name;
            $group = $this->player->group;
            $message = "chat`$name`$group`$this->message";
            $this->player->chat_count++;
            $this->player->chat_time = time();
            $this->room->sendChat($message, $this->from_id); // send message
        }
    }


    // advanced information for admins
    private function commandAdminDebug()
    {
        global $pdo, $server_id, $server_name, $uptime, $port,
            $guild_id, $guild_owner, $server_expire_time, $campaign_array;

        $is_ps = ($guild_id !== 0 && $guild_id !== 183) ? 'yes' : 'no';
        $args = explode(' ', $this->message);
        array_shift($args);
        $debug_arg = strtolower($args[0]);
        if ($debug_arg === 'help') {
            $this->write(
                "systemChat`Acceptable Arguments:<br><br>".
                "- help<br>".
                "- campaign [dump <b>|</b> refresh]<br>".
                "- player (*name*)<br>".
                "- server"
            );
        } elseif ($debug_arg === 'server') {
            $server_expires = $is_ps === 'no' ? 'never' : $server_expire_time;
            $this->write(
                "message`chat_message: $this->message<br>".
                "server_id: $server_id<br>".
                "server_name: $server_name<br>".
                "uptime: $uptime<br>".
                "port: $port<br>".
                "private_server: $is_ps<br>".
                "server_guild: $guild_id<br>".
                "server_owner: $guild_owner<br>".
                "server_expire_time: $server_expires"
            );
        } elseif ($debug_arg === 'campaign') {
            $campaign_arg = strtolower((string) @$args[1]);
            if ($campaign_arg === 'dump') {
                $ret = json_encode($campaign_array);
            } elseif ($campaign_arg === 'refresh') {
                $new_campaign = campaign_select($pdo);
                set_campaign($new_campaign);
                $ret = "Great success! Campaign data refreshed. New:\n\n" . json_encode($new_campaign);
            } else {
                $campaign = json_decode(json_encode($campaign_array));
                $ret = "";
                foreach (range(1, 6) as $campaign_id) {
                    ${"c" . $campaign_id . "_arr"} = array();
                }
                foreach ($campaign as $level) {
                    array_push(${"c" . $level->campaign . "_arr"}, $level);
                }
                foreach (range(1, 6) as $campaign_id) {
                    $levels = json_decode(json_encode(${"c" . $campaign_id . "_arr"}));
                    if (empty($levels)) {
                        $ret .= "No levels for campaign #$campaign_id.<br><br>";
                    } else {
                        usort($levels, array($this, "orderCampaignLevels"));
                        $ret .= "Campaign #$campaign_id:";
                        foreach ($levels as $level) {
                            $ret .= "<br>Level $level->level_num (ID: $level->level_id)"
                                ." | Prize: $level->prize_type #$level->prize_id ($level->prize)";
                        }
                        $ret .= '<br><br>';
                    }
                }
            }
            $this->write("message`$ret");
        } elseif ($debug_arg === 'player') {
            $player_name = trim(substr($this->message, 14));
            $player = name_to_player($player_name);

            if (isset($player)) {
                $pip = $player->ip;
                $pname = $player->name;
                $puid = $player->user_id;
                $pstatus = $player->status;
                $pgroup = $player->group;
                $pguild = $player->guild_id;
                $parank = $player->active_rank;
                $prank = $player->rank;
                $prtused = $player->rt_used;
                $prtavail = $player->rt_available;
                $pexp2day = $player->exp_today;
                $pexppoints = $player->exp_points;
                $pspeed = $player->speed;
                $paccel = $player->acceleration;
                $pjump = $player->jumping;
                $phat = $player->hat;
                $phead = $player->head;
                $pbody = $player->body;
                $pfeet = $player->feet;
                $phatc = $player->hat_color;
                $pheadc = $player->head_color;
                $pbodyc = $player->body_color;
                $pfeetc = $player->feet_color;
                $pehatc = $player->hat_color_2;
                $peheadc = $player->head_color_2;
                $pebodyc = $player->body_color_2;
                $pefeetc = $player->feet_color_2;
                $pdomain = $player->domain;
                $pversion = $player->version;
                $plaction = $player->socket->last_user_action;
                $plaction = format_duration(time() - $plaction) . " ago ($plaction)";
                $plexp = format_duration(time() - $player->last_exp_time) . " ago ($player->last_exp_time)";
                $ptemp = $player->temp_mod === true ? 'yes' : 'no';
                $pso = $player->server_owner === true ? 'yes' : 'no';

                $this->write(
                    "message`chat_message: $this->message<br>"
                    ."ip: $pip<br>"
                    ."name: $pname | user_id: $puid<br>"
                    ."status: $pstatus<br>"
                    ."last_user_action: $plaction<br>"
                    ."group: $pgroup | temp_mod: $ptemp | server_owner: $pso<br>"
                    ."guild_id: $pguild<br>"
                    ."active_rank: $parank | rank (no rt): $prank | rt_used: $prtused | rt_avail: $prtavail<br>"
                    ."exp_today: $pexp2day | exp_points: $pexppoints<br>"
                    ."last_exp_time: $plexp<br>"
                    ."speed: $pspeed | acceleration: $paccel | jumping: $pjump<br>"
                    ."hat: $phat | head: $phead | body: $pbody | feet: $pfeet<br>"
                    ."hat_color: $phatc | hat_color_2: $pehatc<br>"
                    ."head_color: $pheadc | head_color_2: $peheadc<br>"
                    ."body_color: $pbodyc | body_color_2: $pebodyc<br>"
                    ."feet_color: $pfeetc | feet_color_2: $pefeetc<br>"
                    ."domain: $pdomain<br>"
                    ."version: $pversion"
                );
            } else {
                $this->write('message`Error: Could not find a player with that name on this server.');
            }
        } else {
            $message = "Enter an argument to get the data you want. For a list of arguments, type /debug help.";
            $this->write("systemChat`$message");
        }
    }


    // fake "promote" command for silly admins
    private function commandAdminFakePromote()
    {
        $promote_this = trim(substr($this->message, 9));
        $safe_promote_this = htmlspecialchars($promote_this, ENT_QUOTES); // html killer
        if (strlen($safe_promote_this) >= 1) {
            $admin_url = userify($this->player, $this->player->name);
            $this->room->sendChat("systemChat`$admin_url has promoted $safe_promote_this");
        } else {
            $this->write('systemChat`The thing you\'re promoting must be at least 1 character.');
        }
    }


    // server restart for admins
    private function commandAdminRestartServer()
    {
        global $pdo, $server_name;

        if ($this->room_type === 'c') {
            if ($this->player->restart_warned === false) {
                $this->player->restart_warned = true;
                $this->write(
                    'systemChat`WARNING: You just typed the server restart command. '.
                    'If you choose to proceed, this action will disconnect EVERY player on this server. '.
                    'Are you sure you want to disconnect ALL players and restart the server? '.
                    'If so, type the command again.'
                );
            } elseif ($this->player->restart_warned === true) {
                $name_str = (string) $this->player->name;
                $ip_str = (string) $this->player->ip;
                $message = "$name_str ($this->from_id) restarted $server_name from $ip_str.";
                admin_action_insert($pdo, $this->from_id, $message, $this->from_id, $this->player->ip);
                output("$name_str ($this->from_id) initiated a server shutdown.");
                $this->write('systemChat`Restarting...');
                restart_server();
            }
        } else {
            $this->write('systemChat`This command cannot be used in levels.');
        }
    }


    // server mod management for server owners
    private function commandSOPromoteMod()
    {
        $msg_lower = strtolower($this->message);
        if ($msg_lower === '/mod help') {
            $help_msg = 'To promote someone to a server moderator, type "/mod promote" followed by their username. '.
                'They will be a server moderator until they log out or are demoted. '.
                'To demote an existing server moderator, type "/mod demote" followed by their username.';
            $this->write("systemChat`$help_msg");
        } elseif (strpos($msg_lower, '/mod promote ') === 0 || strpos($msg_lower, '/mod demote ') === 0) {
            if (strpos($msg_lower, '/mod promote ') === 0) {
                $action = 'promote';
                $to_name = trim(substr($this->message, 13));
            } elseif (strpos($msg_lower, '/mod demote ') === 0) {
                $action = 'demote';
                $to_name = trim(substr($this->message, 12));
            }
            $target = name_to_player($to_name);

            // do the appropriate action
            if ($action === 'promote') {
                promote_server_mod($to_name, $this->player, $target);
            } elseif ($action === 'demote') {
                demote_server_mod($to_name, $this->player, $target);
            }
        } else {
            $this->write('systemChat`Invalid input. For more information on how to use this command, type /mod help.');
        }
    }


    // gets time left for server owners
    private function commandSOTimeLeft()
    {
        global $guild_id, $server_expire_time;

        if ($guild_id !== 0 && $guild_id !== 183) {
            $this->write(
                "systemChat`Your server will expire on $server_expire_time. ".
                "To extend your time, buy either Private Server 1 or Private Server 30 from the Vault of Magics."
            );
        } else {
            $this->write("systemChat`This is not a private server.");
        }
    }


    // chat announcement
    private function commandModAnnouncement()
    {
        $announcement = trim(substr($this->message, 3));
        $safe_announcement = htmlspecialchars($announcement, ENT_QUOTES); // html killer
        if (strlen($safe_announcement) >= 1) {
            $mod_url = userify($this->player, $this->player->name);
            $this->room->sendChat("systemChat`Chatroom Announcement from $mod_url: $safe_announcement");
        } else {
            $this->write('systemChat`Your announcement must be at least 1 character.');
        }
    }


    // clears the chatroom
    private function commandModClearChatroom()
    {
        if ($this->room == $this->player->chat_room) {
            $this->room->clear($this->player);
        } else {
            $this->write('systemChat`This command cannot be used in levels.');
        }
    }


    // disconnects a player without disciplining them
    private function commandModDisconnect()
    {
        global $pdo, $server_name;

        $msg_lower = strtolower($this->message);
        $dc_name = trim(substr($this->message, 12)); // for /disconnect
        $dc_name = strpos($msg_lower, '/dc ') === 0 ? trim(substr($this->message, 4)) : $dc_name; // for /dc
        $safe_dc_name = htmlspecialchars($dc_name, ENT_QUOTES);

        // disconnect the user
        $dc_player = name_to_player($dc_name);
        if (isset($dc_player) && ($dc_player->group < $this->player->group || $this->isTempMod($dc_player) === true)) {
            // do it and tell the world
            $dc_player->remove();
            $this->write("message`$safe_dc_name has been disconnected.");

            // log if a mod
            if ($this->isServerOwner() === false || $this->from_id === FRED) {
                $mod_name = $this->player->name;
                $mod_id = $this->from_id;
                $mod_ip = $this->player->ip;
                $message = "$mod_name disconnected $dc_name from $server_name from $mod_ip.";
                mod_action_insert($pdo, $mod_id, $message, $mod_id, $mod_ip);
            }
        } elseif (isset($dc_player) && $dc_player->group >= $this->player->group) {
            $this->write("message`Error: You lack the power to disconnect $safe_dc_name.");
        } else {
            $this->write("message`Error: Could not find a user with the name \"$safe_dc_name\" on this server.");
        }
    }


    // fake "given" command for silly mods
    private function commandModFakeGive()
    {
        $give_this = trim(substr($this->message, 6));
        $safe_give_this = htmlspecialchars($give_this, ENT_QUOTES); // html killer
        if (strlen($safe_give_this) >= 1) {
            $mod_url = userify($this->player, $this->player->name);
            $this->room->sendChat("systemChat`$mod_url has given $safe_give_this");
        } else {
            $this->write('systemChat`The thing you\'re giving must be at least 1 character.');
        }
    }


    // kicks a user for 30 minutes
    private function commandModKick()
    {
        $kicked_name = trim(substr($this->message, 6));
        client_kick($this->player->socket, $kicked_name);
    }


    // "unkicks" a kicked user
    private function commandModUnKick()
    {
        $unkicked_name = trim(substr($this->message, 8));
        client_unkick($this->player->socket, $unkicked_name);
    }


    // unmuted a muted user
    private function commandModUnMute()
    {
        $unmuted_name = trim(substr($this->message, 8));
        client_unmute($this->player->socket, $unmuted_name);
    }


    // activate an html text effect in this chatroom
    private function commandModTextEffect()
    {
        // switch for text effects
        switch (strtolower($this->message)) {
            case '/b':
                $effect = 'bold';
                $tag = '<b>';
                break;
            case '/i':
                $effect = 'italicized';
                $tag = '<i>';
                break;
            case '/u':
                $effect = 'underlined';
                $tag = '<u>';
                break;
            case '/li':
                $effect = 'bulleted';
                $tag = '<li>';
                break;
            default:
                $effect = null;
                $tag = null;
        }

        if (isset($effect)) {
            if ($this->room_type === 'c') {
                $player_link = userify($this->player, $this->player->name);
                $this->room->sendChat("systemChat`$tag$player_link has temporarily activated $effect chat!");
            } else {
                $this->write('systemChat`This command cannot be used in levels.');
            }
        } else {
            $this->write('message`Error: Invalid text effect specified.');
        }
    }


    // view ban priors for a user
    private function commandModViewPriors()
    {
        global $pdo;

        $name = trim(substr($this->message, 8));
        get_priors($pdo, $this->player, $name);
    }


    // be awesome
    private function commandBeAwesome()
    {
        $this->write("message`<b>You're awesome!</b>");
    }


    // change happy hour settings (admins only) or check status
    private function commandHappyHour()
    {
        $args = explode(' ', $this->message);
        array_shift($args);

        $args[0] = strtolower($args[0]);
        if ($this->message === '/hh' || $args[0] === 'status') {
            $hh_timeleft = HappyHour::timeLeft();
            if ($hh_timeleft !== false) {
                $left = format_duration($hh_timeleft);
                $this->write("systemChat`A Happy Hour is currently active on this server! It will expire in $left.");
            } else {
                $this->write('systemChat`There is not currently a Happy Hour on this server.');
            }
        } elseif ($args[0] === 'help') {
            $hhmsg_status = 'systemChat`To find out if a Happy Hour is active and when it expires, type /hh status.';
            $hhmsg_admin = '';
            $hhmsg_server_owner = '';
            $hhmsg_warning = 'WARNING: This will delete all pending purchased Happy Hours and end the current one.';
            if ($this->isAdmin() === true && $this->isServerOwner() === false) {
                $hhmsg_admin = "To activate a Happy Hour, type /hh activate. " .
                    "To deactivate the current Happy Hour, type /hh deactivate. $hhmsg_warning";
            } elseif ($this->isServerOwner() === true) {
                $hhmsg_server_owner = "To deactivate the current Happy Hour, type /hh deactivate. $hhmsg_warning";
            }
            $this->write("$hhmsg_status $hhmsg_admin $hhmsg_server_owner");
        } elseif ($args[0] === 'activate' && $this->isAdmin() === true) {
            if (HappyHour::isActive() !== true && PR2SocketServer::$tournament === false) {
                if (!isset($args[1])) {
                    HappyHour::activate();
                } else {
                    $args[1] = (int) $args[1];
                    $args[1] = $args[1] > 3600 ? 3600 : $args[1];
                    HappyHour::activate($args[1]);
                }
                $me_url = userify($this->player, $this->player->name);
                $this->room->sendChat("systemChat`$me_url just triggered a Happy Hour!");
            } elseif (PR2SocketServer::$tournament === true) {
                $ret = 'Tournament mode must be off to activate a Happy Hour. Disable tournament mode and try again.';
                $this->write("systemChat`$ret");
            } else {
                $time_left = format_duration(HappyHour::timeLeft());
                $this->write("systemChat`There is already a Happy Hour on this server. It will expire in $time_left.");
            }
        } elseif ($args[0] === 'deactivate' && ($this->isAdmin() === true || $this->isServerOwner() === true)) {
            if ($this->player->hh_warned === false) {
                $this->player->hh_warned = true;
                $ret = 'WARNING: This will delete all pending purchased Happy Hours and end the current one. '.
                    'If you\'re sure you want to do this, type the command again.';
                $this->write("systemChat`$ret");
            } elseif (HappyHour::isActive() && $this->player->hh_warned) {
                HappyHour::deactivate();
                $me_url = userify($this->player, $this->player->name);
                $this->room->sendChat("systemChat`$me_url just ended the current Happy Hour.");
            } else {
                $this->write('systemChat`There isn\'t an active Happy Hour right now.');
            }
        } else {
            $this->write('systemChat`Error: Invalid argument specified. Type /hh help for more information.');
        }
    }


    // gets the current server population
    private function commandPopulation()
    {
        global $player_array;

        $pop_counted = count($player_array);
        $pop_singular = array("is", "user");
        $pop_plural = array("are", "users");
        $lang = $pop_counted === 1 ? $pop_singular : $pop_plural;
        $this->write("systemChat`There $lang[0] currently $pop_counted $lang[1] playing on this server.");
    }


    // change tournament settings (server owner only) or check status
    private function commandTournament()
    {
        $msg_lower = strtolower($this->message);
        // if server owner, allow them to do server owner things
        if ($this->player->server_owner === true) {
            if ($msg_lower === '/t help' || $msg_lower === '/t' || $msg_lower === '/tournament') {
                $this->write('systemChat`Welcome to tournament mode!<br><br>'.
                    'To enable a tournament, use /t followed by a hat '.
                    'name and stat values for the desired speed, '.
                    'acceleration, and jumping of the tournament.<br><br>'.
                    'Example: /t none 65 65 65<br>Hat: None<br>'.
                    'Speed: 65<br>'.
                    'Accel: 65<br>'.
                    'Jump: 65<br><br>'.
                    'To turn off tournament mode, type /t off. '.
                    'To find out whether tournament mode is on or off, '.
                    'type /t status.');
            } elseif ($msg_lower === '/t status') {
                tournament_status($this->player);
            } else {
                try {
                    issue_tournament(htmlspecialchars($this->message, ENT_QUOTES));
                    announce_tournament($this->room);
                } catch (\Exception $e) {
                    $err = $e->getMessage();
                    $err_supl = "Make sure you typed everything correctly! For help with tournaments, type /t help.";
                    $this->write("systemChat`Error: $err $err_supl");
                }
            }
        } // if not the server owner, limit their ability to checking the status of a tournament only
        else {
            if ($msg_lower === '/t status' || $msg_lower === '/t' || $msg_lower === '/tournament') {
                tournament_status($this->player);
            } else {
                $this->write('systemChat`To find out whether tournament mode is on or off, type /t status.');
            }
        }
    }


    // view rules
    private function commandViewRules()
    {
        global $guild_id;

        $rules_link = urlify('https://pr2hub.com/rules', 'pr2hub.com/rules');
        $message = "The PR2 rules can be found at $rules_link.";
        if ($guild_id !== 0) {
            $message .= ' Since this is a private server, your guild owner may have different rules'.
                ' for the chatrooms and the server. Check with them if you\'re unsure.';
        }
        $this->write("systemChat`$message");
    }


    // view emoticons
    private function commandViewEmotes()
    {
        if ($this->room_type === 'c') {
            $this->write(
                'systemChat`PR2 Emoticons:<br>'
                .':shrug: = ‾\_(ツ)_/‾<br>'
                .':lenny: = ( ͡° ͜ʖ ͡°)<br>'
                .':yay: = ╰(ᴖ◡ᴖ)╯<br>'
                .':hello: = ー( ◉▽◉ )ﾉ<br>'
                .':think: = 🤔<br>'
                .':laugh: = 😂<br>'
                .':hooray: = 🎉<br>'
                .':fred: = 🌵<br>'
            );
        } else {
            $ret = 'To get a list of usable emoticons, go to the chat tab in the lobby and type /emotes.';
            $this->write("systemChat`$ret");
        }
    }


    // view guides
    private function commandViewGuides()
    {
        if ($this->room_type === 'c') {
            $hats_link = urlify('https://jiggmin2.com/forums/showthread.php?tid=122', 'Hats');
            $eups_link = urlify('https://jiggmin2.com/forums/showthread.php?tid=123', 'Epic Upgrades');
            $groups_link = urlify('https://jiggmin2.com/forums/showthread.php?tid=146', 'Groups');
            $fah_link = urlify('https://jiggmin2.com/forums/showthread.php?tid=19', 'Folding at Home (F@H)');
            $this->write(
                'systemChat`Helpful Resources:<br>'
                ."- $hats_link<br>"
                ."- $eups_link<br>"
                ."- $groups_link<br>"
                ."- $fah_link"
            );
        } else {
            $ret = 'To get a list of PR2-related guides, go to the chat tab in the lobby and type /guides.';
            $this->write("systemChat`$ret");
        }
    }


    // view commands
    private function commandViewHelp()
    {
        $mod = '';
        $effects = '';
        $admin = '';
        $server_owner = '';

        if ($this->room_type === 'g') {
            $this->write('systemChat`To get a list of chat commands, go to the chat tab in the lobby and type /help.');
        } else {
            if ($this->isMod() === true) {
                $mod = '<br>Moderator:<br>'.
                    '- /a *announcement*<br>'.
                    '- /give *message*<br>'.
                    '- /kick *name*<br>'.
                    '- /unkick *name*<br>'.
                    '- /disconnect *name*<br>'.
                    '- /priors *name*<br>'.
                    '- /clear';
                $effects = '<br>Chat Effects:<br>'.
                    '- /b (Bold)<br>'.
                    '- /i (Italics)<br>'.
                    '- /u (Underlined)<br>'.
                    '- /li (Bulleted)';
            }
            if ($this->isAdmin() === true) {
                $admin = '<br>Admin:<br>'.
                    '- /promote *message*<br>'.
                    '- /restart_server<br>'.
                    '- /debug *arg*<br>'.
                    '- /hh help';
            }
            if ($this->isServerOwner() === true) {
                $server_owner = '<br>Server Owner:<br>'.
                    '- /timeleft<br>'.
                    '- /mod help<br>'.
                    '- /hh help<br>'.
                    '- /t (Tournament)<br>'.
                    'For more information on tournaments, use /t help.';
            }
            $this->write('systemChat`PR2 Chat Commands:<br>'.
                '- /rules<br>'.
                '- /here (in this chatroom)<br>'.
                '- /view *player*<br>'.
                '- /guild *guild name*<br>'.
                '- /pm *player*<br>'.
                '- /hint (Artifact)<br>'.
                '- /hh status<br>'.
                '- /t status<br>'.
                '- /population<br>'.
                '- /beawesome<br>'.
                '- /emotes<br>'.
                '- /guides'.$mod.$effects.$admin.$server_owner);
        }
    }


    // find who is in the current chatroom
    private function commandWhoIsHere()
    {
        if ($this->room === $this->player->chat_room) {
            $this->write('systemChat`' . $this->room->whoIsHere());
        } else {
            $this->write('systemChat`This command cannot be used in levels.');
        }
    }


    // returns true if the user is a temp mod
    private function isTempMod($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return ($player->group === 2 && $player->temp_mod === true) ? true : false;
    }


    // returns true if the user is a mod or higher (including server owner)
    private function isMod($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return ($player->group >= 2 && $player->temp_mod === false) ? true : false;
    }


    // returns true if the user is an admin (not server owner)
    private function isAdmin($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return ($player->group === 3 && $player->server_owner === false) ? true : false;
    }


    // returns true if the user is the server owner with a power of 3
    private function isServerOwner($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return ($player->group === 3 && $player->server_owner === true) ? true : false;
    }


    // order campaign levels
    protected function orderCampaignLevels($a, $b)
    {
        if ($a->level_num === $b->level_num) {
            return 0;
        }
        return $a->level_num < $b->level_num ? -1 : 1;
    }


    // shorter version of writing to the player
    private function write($message)
    {
        $this->player->write($message);
    }


    public function __destruct()
    {
        // delete
        foreach ($this as $key => $var) {
            $this->$key = null;
            unset($this->$key, $key, $var);
        }
    }
}
