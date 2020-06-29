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
        $this->message = str_ireplace([':thumbsup:', ':+1:'], '👍', $this->message);
        $this->message = str_ireplace([':thumbsdown:', ':-1:'], '👎', $this->message);
        $this->message = str_ireplace([':thinking:', ':think:', ':what:', ':hmm:'], '🤔', $this->message);
        $this->message = str_ireplace([':eyes:', ':eye:', ':00:'], '👀', $this->message);
        $this->message = str_ireplace([':lol:', ':laugh:', ':lmao:', ':joy:'], '😂', $this->message);
        $this->message = str_ireplace([':hooray:', ':tada:', ':party:'], '🎉', $this->message);
        $this->message = str_ireplace([':fred:', ':cactus:'], '🌵', $this->message);
        $this->message = str_ireplace([':clown:', ':jmack:'], '🤡', $this->message);
        $this->message = str_ireplace([':waving:', ':waving-hand:', ':wave:'], '👋', $this->message);
        $this->message = str_ireplace(':dragon:', '🐉', $this->message);
        $this->message = str_ireplace(':hammer:', '🔨', $this->message);
        $this->message = str_ireplace([':sunglasses:', ':cool:'], '😎', $this->message);
        $this->message = str_ireplace(':100:', '💯', $this->message);
        $this->message = str_ireplace([':pointup:', ':this:', ':^:'], '☝️', $this->message);
        $this->message = str_ireplace([':upside-down-face:', ':udf:'], '🙃', $this->message);
        $this->message = str_ireplace([':ok-hand:', ':ok:'], '👌', $this->message);
        $this->message = str_ireplace(':whale:', '🐋', $this->message);
        $this->message = str_ireplace(':finish:', '🏁', $this->message);
        $this->message = str_ireplace([':plead:', ':plz:'], '🥺', $this->message);
        $this->message = str_ireplace([':sob:', ':cry:'], '😭', $this->message);
        $this->message = str_ireplace(':money:', '💸', $this->message);
        $this->message = str_ireplace(':clap:', '👏', $this->message);
    }


    // handle a chat command
    private function handleCommand()
    {
        global $guild_id, $guild_owner;

        $t_arr = ['t', 'tournament'];
        $effect_arr = ['b', 'u', 'i', 'li'];
        $emotes_arr = ['emote', 'emotes', 'emoticons', 'emojis', 'smilies', 'smiles'];
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
            } elseif ($msg === '/kicked') {
                $this->commandModWhoIsKicked();
                $handled = true;
            } elseif (strpos($msg, '/kick ') === 0) {
                $this->commandModKick(); // kick for mods
                $handled = true;
            } elseif (strpos($msg, '/unkick ') === 0) {
                $this->commandModUnKick(); // unkick for mods
                $handled = true;
            } elseif (strpos($msg, '/mute ') === 0 || strpos($msg, '/warn ') === 0) {
                $this->commandModMute(); // mute for mods
                $handled = true;
            } elseif ($msg === '/muted' || $msg === '/warned') {
                $this->commandModWhoIsMuted();
                $handled = true;
            } elseif (strpos($msg, '/unmute ') === 0 || strpos($msg, '/unwarn ') === 0) {
                $this->commandModUnMute(); // unmute for mods
                $handled = true;
            } elseif ((strpos($msg, '/dc ') === 0 || strpos($msg, '/disconnect ') === 0)) {
                $this->commandModDisconnect(); // dc for mods
                $handled = true;
            } elseif (strpos($msg, '/priors ') === 0 && ($this->isServerOwner() === false || $guild_id === 183)) {
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
            } elseif (strpos($msg, '/prizer ') === 0) {
                $this->commandAdminSetPrizer(); // change prizer for admins
                $handled = true;
            }
        }

        // for prizer
        if ($this->player->user_id === PR2SocketServer::$prizer_id) {
            if (strpos($msg, '/set ') === 0) {
                $this->commandPrizerSetPrize();
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
        } elseif ($msg === '/community') {
            $this->commandCommunity(); // community links
            $handled = true;
        } elseif ($msg === '/contests' || $msg === '/contest') {
            $this->commandContests(); // contests link
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
        global $guild_id;

        $player = $this->player;
        $muted = Mutes::isMuted($player->name, $player->ip);

        // make sure they're allowed to send a message
        if ($player->group <= 0 || $player->guest === true) {
            $this->write('systemChat`Sorries, guests can\'t send chat messages.'); // guest check
        } elseif ($player->active_rank < 3 && $player->group < 2) {
            $this->write('systemChat`Sorries, you must be rank 3 or above to chat.'); // rank 3 check
        } elseif ($this->isSociallyBanned()) {
            $msg = $this->outputSocialBan();
            $this->write("systemChat`$msg");
        } elseif ($muted && ((!$this->isMod() && !$this->isTempMod()) || (!$this->isServerOwner() && $guild_id > 0))) {
            $cb_secs = (int) Mutes::remainingTime($player->name, $player->ip);
            $ret = "You have been temporarily muted from the chat. The mute will be lifted in $cb_secs seconds.";
            $this->write("systemChat`$ret"); // muted check (warnings, auto-warn, manual mute duration)
        } elseif ($player->getChatCount() > 6 && ($player->group < 2 || $this->isTempMod() === true)) {
            Mutes::add($player->name, $player->ip, 60);
            $this->write('systemChat`Slow down a bit, yo.'); // spamming check
        } elseif (strpos($player->name, '`') !== false || strpos($this->message, '`') !== false) {
            $this->write('message`Error: Illegal character detected.'); // illegal character in username/message check
        } else {
            $name = $this->player->name;
            $group = group_str($this->player);
            $message = "chat`$name`$group`$this->message";
            $player->chat_count++;
            $player->chat_time = time();
            $this->room->sendChat($message, $this->from_id); // send message
        }
    }


    // outputs a social ban message to a player
    private function outputSocialBan()
    {
        $exp_time = \format_duration($this->player->sban_exp_time - time());
        $msg = "This account or IP address has been socially banned. It will expire in about $exp_time.";
        if ($this->room_type === 'c') {
            $ban_id = (int) $this->player->sban_id;
            $ban_url = \urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", 'here');
            $dispute_url = \urlify("https://jiggmin2.com/forums/showthread.php?tid=110", 'dispute it');
            $msg .= " You can view more details $ban_url. If you feel this ban is unjust, you can $dispute_url.";
        }
        return $msg;
    }


    // advanced information for admins
    private function commandAdminDebug()
    {
        global $server_id, $server_name, $uptime, $port,
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
                "- server [info <b>|</b> restart]"
            );
        } elseif ($debug_arg === 'server') {
            $server_arg = strtolower((string) @$args[1]);
            if ($server_arg === 'restart') {
                global $server_name;
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
                        $msg = "$name_str ($this->from_id) restarted $server_name from $ip_str.";
                        db_op('admin_action_insert', array($this->from_id, $msg, 'restart-server', $this->player->ip));
                        output("$name_str ($this->from_id) initiated a server shutdown.");
                        $this->write('systemChat`Restarting...');
                        restart_server();
                    }
                } else {
                    $this->write('systemChat`This command cannot be used in levels.');
                }
            } else {
                $server_expires = $is_ps === 'no' ? 'never' : $server_expire_time;
                $prizer = PR2SocketServer::$prizer_id;
                $this->write(
                    "message`chat_message: $this->message<br>".
                    "id: $server_id<br>".
                    "name: $server_name<br>".
                    "port: $port<br>".
                    "uptime: $uptime<br>".
                    "expire_time: $server_expires<br>".
                    "private_server: $is_ps<br>".
                    "guild_id: $guild_id<br>".
                    "guild_owner: $guild_owner<br>".
                    'happy_hour: ' . HappyHour::$random_hour . '<br>'.
                    "prizer: $prizer<br>"
                );
            }
        } elseif ($debug_arg === 'campaign') {
            $campaign_arg = strtolower((string) @$args[1]);
            if ($campaign_arg === 'dump') {
                $ret = json_encode($campaign_array);
            } elseif ($campaign_arg === 'refresh') {
                $new_campaign = db_op('campaign_select');
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
                $phatc = strtoupper(base_convert($player->hat_color, 10, 16));
                $pheadc = strtoupper(base_convert($player->head_color, 10, 16));
                $pbodyc = strtoupper(base_convert($player->body_color, 10, 16));
                $pfeetc = strtoupper(base_convert($player->feet_color, 10, 16));
                $pehatc = strtoupper(base_convert($player->hat_color_2, 10, 16));
                $peheadc = strtoupper(base_convert($player->head_color_2, 10, 16));
                $pebodyc = strtoupper(base_convert($player->body_color_2, 10, 16));
                $pefeetc = strtoupper(base_convert($player->feet_color_2, 10, 16));
                $pdomain = $player->domain;
                $pversion = $player->version;
                $plaction = $player->socket->last_user_action;
                $plaction = format_duration(time() - $plaction) . " ago ($plaction)";
                $plexp = format_duration(time() - $player->last_exp_time) . " ago ($player->last_exp_time)";
                $ptemp = $player->temp_mod ? 'yes' : 'no';
                $pso = $player->server_owner ? 'yes' : 'no';
                $psb = $player->sban_exp_time - time() > 0 ? 'yes' : 'no';
                $psbid = $player->sban_id;
                $psbet = format_duration($player->sban_exp_time - time()) . " ($player->sban_exp_time)";

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
                    ."hat_color: #$phatc | hat_color_2: #$pehatc<br>"
                    ."head_color: #$pheadc | head_color_2: #$peheadc<br>"
                    ."body_color: #$pbodyc | body_color_2: #$pebodyc<br>"
                    ."feet_color: #$pfeetc | feet_color_2: #$pefeetc<br>"
                    ."socially_banned: $psb" . ($psbet > 0 ? " | id: $psbid | exp_time: $psbet" : '') . '<br>'
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


    // sets a privileged account (prizer) that can change a prize in-game
    private function commandAdminSetPrizer()
    {
        $prizer_id = (int) trim(substr($this->message, 8));

        // sanity: valid input?
        if ($prizer_id === 0) {
            PR2SocketServer::$prizer_id = 0;
            $this->write('systemChat`The prizer has been disabled.');
            return;
        }

        // make sure prizer exists
        $new_prizer = db_op('user_select_name_and_power', array($prizer_id, true));
        if ($new_prizer === false) {
            $this->write('systemChat`Error: Could not find a user with that ID.');
            return;
        }

        // sanity: are they a guest?
        if ((int) $new_prizer->power === 0) {
            $this->write('systemChat`Error: You can\'t make a guest the prizer.');
            return;
        }

        // if the old prizer is online, tell their client it's over
        $old_prizer = id_to_player(PR2SocketServer::$prizer_id, false);
        if (isset($old_prizer)) {
            $old_prizer->write('demotePrizer`');
        }

        // set new prizer
        PR2SocketServer::$prizer_id = $prizer_id;
        $new_prizer = userify(null, $new_prizer->name, $new_prizer->power);
        $this->write("systemChat`Great success! The prizer has been set to $new_prizer.");

        // if the new prizer is online, tell their client it's time
        $new_prizer = id_to_player(PR2SocketServer::$prizer_id, false);
        if (isset($new_prizer)) {
            $new_prizer->write('becomePrizer`');
        }
    }


    private function commandPrizerSetPrize()
    {
        if ($this->room_type !== 'g') {
            $this->write('systemChat`To set a prize, enter a level and type /set *type* *id*.');
        }

        // get and send args to game room
        $args = explode(' ', $this->message);
        array_shift($args);
        $this->player->game_room->prizerSetPrize($this->player->user_id, @strtolower($args[0]), (int) $args[1]);
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


    // shows who is currently kicked from the server
    public function commandModWhoIsKicked()
    {
        $kicks = \pr2\multi\ServerBans::getAll();
        $count = count($kicks);
        $str = "Currently kicked from this server ($count):"; // start the return string
    
        foreach ($kicks as $kick) {
            $time_remaining = format_duration(\pr2\multi\ServerBans::remainingTime($kick->user_name, $kick->ip));
            $str .= "<br> - " . userify(null, $kick->user_name) . ' (' . $time_remaining . ')';
        }

        // this should never happen (the person in the room is calling the function)
        if ($str === 'Currently kicked from this server (0):') {
            $str = 'No one is kicked! \0/';
        }

        // talk about unkicking
        $str .= '<br /><br />To remove a kick, type:<br />/unkick *name*';
        
        // send the string back
        $this->write("systemChat`$str");
    }


    // disconnects a player without disciplining them
    private function commandModDisconnect()
    {
        global $server_name;

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
                db_op('mod_action_insert', array($mod_id, $message, 'dc', $mod_ip));
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


    // mutes/warns a user
    private function commandModMute()
    {
        $data = explode(' ', $this->message);
        $offset = ((int) $data[1] > 0 || (int) $data[1] == $data[1]) && strlen($data[1]) === 1 ? 8 : 6;
        $warn_num = $offset === 8 && (int) $data[1] <= 3 ? (int) $data[1] : 1;
        $target_name = trim(substr($this->message, $offset));
        client_warn($this->player->socket, "$target_name`$warn_num");
    }


    // shows who is currently warned/muted
    public function commandModWhoIsMuted()
    {
        $mutes = \pr2\multi\Mutes::getAll();
        $count = count($mutes);
        $str = "Currently muted ($count):"; // start the return string

        foreach ($mutes as $mute) {
            $time_remaining = format_duration(\pr2\multi\Mutes::remainingTime($mute->user_name, $mute->ip));
            $str .= "<br> - " . userify(null, $mute->user_name) . ' (' . $time_remaining . ')';
        }

        // this should never happen (the person in the room is calling the function)
        if ($str === 'Currently muted (0):') {
            $str = 'No one is muted! \0/';
        }

        // talk about unmuting
        $str .= '<br /><br />To remove a mute, type:<br />/unmute *name*';

        // send the string back
        $this->write("systemChat`$str");
    }


    // unmutes a muted user
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
        $name = trim(substr($this->message, 8));
        get_priors($this->player, $name);
    }


    // be awesome
    private function commandBeAwesome()
    {
        $this->write("message`<b>You're awesome!</b>");
    }

    // community command (links to JV2, discord)
    private function commandCommunity()
    {
        $msg = 'systemChat`Join the community!<br>'
            .'<br> - '.urlify('https://jiggmin2.com/forums', 'Jiggmin\'s Village')
            .'<br> - '.urlify('https://discord.gg/kcWBBBj', 'JV Discord');
        $this->write($msg);
    }

    // contests command (links to contests hub)
    private function commandContests()
    {
        $msg = $this->isSociallyBanned() ? $this->outputSocialBan() : 'systemChat`'
            .'PR2 has a variety of contests in which you can participate to earn in-game prizes! '
            .'For more information, visit ' . urlify('https://pr2hub.com/contests', 'pr2hub.com/contests') . '.';
        $this->write($msg);
    }

    // change happy hour settings (admins only) or check status
    private function commandHappyHour()
    {
        $args = explode(' ', $this->message);
        array_shift($args);

        $args[0] = @strtolower($args[0]);
        if ($args[0] === 'help') {
            $hhmsg_status = 'systemChat`To find out if a Happy Hour is active and when it expires, type /hh.';
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
            $hh_timeleft = HappyHour::timeLeft();
            if ($hh_timeleft !== false) {
                $left = format_duration($hh_timeleft);
                $this->write("systemChat`A Happy Hour is currently active on this server! It will expire in $left.");
            } else {
                $this->write('systemChat`There is not currently a Happy Hour on this server.');
            }
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


    // change tournament settings (staff only) or check status
    private function commandTournament()
    {
        global $guild_id;

        $msg_lower = strtolower($this->message);
        // if server owner, allow them to do server owner things
        if ($this->isMod() || ($this->isServerOwner() && $guild_id > 0)) {
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
                'systemChat`Text Emotes:<br>'
                .':shrug: = ‾\_(ツ)_/‾<br>'
                .':lenny: = ( ͡° ͜ʖ ͡°)<br>'
                .':yay: = ╰(ᴖ◡ᴖ)╯<br>'
                .':hello: = ー( ◉▽◉ )ﾉ<br>'
                .'<br>Emojis:<br>'
                .'💯 = :100:<br>'
                .'👍 = :+1:<br>'
                .'👎 = :-1:<br>'
                .'🌵 = :cactus:<br>'
                .'👏 = :clap:<br>'
                .'🤡 = :clown:<br>'
                .'😎 = :cool:<br>'
                .'😭 = :cry:<br>'
                .'🐉 = :dragon:<br>'
                .'👀 = :eyes:<br>'
                .'🏁 = :finish:<br>'
                .'🔨 = :hammer:<br>'
                .'😂 = :laugh:<br>'
                .'💸 = :money:<br>'
                .'👌 = :ok:<br>'
                .'🎉 = :party:<br>'
                .'🥺 = :plead:<br>'
                .'☝️ = :pointup:<br>'
                .'🤔 = :think:<br>'
                .'🙃 = :udf:<br>'
                .'👋 = :waving:<br>'
                .'🐋 = :whale:<br>'
                .'<br>'
                .'If any of these show as boxes, make sure an emoji font is installed on your device.'
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
            $artifact_link = urlify('https://jiggmin2.com/forums/showthread.php?tid=1677', 'Artifact Guide');
            $this->write(
                'systemChat`Helpful Resources:<br>'
                ."- $hats_link<br>"
                ."- $eups_link<br>"
                ."- $groups_link<br>"
                ."- $fah_link<br>"
                ."- $artifact_link"
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
                    '- /kicked (see who\'s kicked)<br>'.
                    '- /kick *name*<br>'.
                    '- /unkick *name*<br>'.
                    '- /mute *num* *name*<br>'.
                    '- /unmute *name*<br>'.
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
                '- /level *level id*<br>'.
                '- /pm *player*<br>'.
                '- /hint (Artifact)<br>'.
                '- /hh status<br>'.
                '- /t status<br>'.
                '- /population<br>'.
                '- /beawesome<br>'.
                '- /emotes<br>'.
                '- /guides<br>'.
                '- /community<br>'.
                '- /contests'.$mod.$effects.$admin.$server_owner);
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
    
    
    // returns true if a user has an active social ban
    private function isSociallyBanned($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return $this->player->sban_exp_time > 0 && $this->player->sban_exp_time - time() > 0;
    }


    // returns true if the user is a temp mod
    private function isTempMod($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return $player->group === 2 && $player->temp_mod === true;
    }


    // returns true if the user is a trial mod
    /*private function isTrialMod($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return $player->group === 2 && $player->trial_mod === true;
    }*/


    // returns true if the user is a mod or higher (including server owner)
    private function isMod($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return $player->group >= 2 && $player->temp_mod === false;
    }


    // returns true if the user is an admin (not server owner)
    private function isAdmin($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return $player->group === 3 && $player->server_owner === false;
    }


    // returns true if the user is the server owner with a power of 3
    private function isServerOwner($player = null)
    {
        $player = isset($player) ? $player : $this->player;
        return $player->group === 3 && $player->server_owner === true;
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
