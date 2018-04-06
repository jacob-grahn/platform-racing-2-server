<?php

require_once __DIR__ . '/../http_server/queries/staff/actions/admin_action_insert.php';
require_once __DIR__ . '/../http_server/queries/staff/actions/mod_action_insert.php';
require_once __DIR__ . '/../http_server/queries/pr2/pr2_update.php';
require_once __DIR__ . '/../http_server/queries/epic_upgrades/epic_upgrades_upsert.php';
require_once __DIR__ . '/../http_server/queries/users/user_update_status.php';
require_once __DIR__ . '/../http_server/queries/rank_tokens/rank_token_update.php';
require_once __DIR__ . '/../http_server/queries/exp_today/exp_today_add.php';

class Player
{
    const FRED = 4291976;

    public $socket;
    public $user_id;
    public $guild_id;

    public $name;
    public $rank;
    public $active_rank;
    public $exp_points;
    public $start_exp_today;
    public $exp_today;
    public $group;
    public $guest;

    public $hat_color;
    public $head_color;
    public $body_color;
    public $feet_color;

    public $hat_color_2;
    public $head_color_2;
    public $body_color_2;
    public $feet_color_2;

    public $hat;
    public $head;
    public $body;
    public $feet;

    public $hat_array = array();
    public $head_array = array();
    public $body_array = array();
    public $feet_array = array();

    public $epic_hat_array = array();
    public $epic_head_array = array();
    public $epic_body_array = array();
    public $epic_feet_array = array();

    public $speed;
    public $acceleration;
    public $jumping;

    public $friends;
    public $ignored;

    public $rt_used;
    public $rt_available;

    public $url = '';
    public $version = '0.0';

    public $last_action = 0;
    public $chat_count = 0;
    public $chat_time = 0;

    public $right_room;
    public $chat_room;
    public $game_room;

    public $course_box;
    public $confirmed = false;
    public $slot;

    public $temp_id;
    public $pos_x = 0;
    public $pos_y = 0;

    public $worn_hat_array = array();
    public $finished_race = false;
    public $quit_race = false;

    public $chat_ban = 0;

    public $domain;
    public $ip;

    public $temp_mod = false;
    public $server_owner = false;

    public $hh_warned = false;
    public $restart_warned = false;

    public $status = '';

    public $lives = 3;
    public $items_used = 0;
    public $super_booster = false;
    public $last_save_time = 0;


    public function __construct($socket, $login)
    {
        $this->socket = $socket;
        $this->ip = $socket->ip;
        $this->last_save_time = time();

        $this->user_id = $login->user->user_id;
        $this->name = $login->user->name;
        $this->group = $login->user->power;
        $this->guild_id = $login->user->guild;

        $this->rank = $login->stats->rank;
        $this->exp_points = $login->stats->exp_points;

        $this->hat_color = $login->stats->hat_color;
        $this->head_color = $login->stats->head_color;
        $this->body_color = $login->stats->body_color;
        $this->feet_color = $login->stats->feet_color;

        $this->hat_color_2 = $login->stats->hat_color_2;
        $this->head_color_2 = $login->stats->head_color_2;
        $this->body_color_2 = $login->stats->body_color_2;
        $this->feet_color_2 = $login->stats->feet_color_2;

        $this->hat = $login->stats->hat;
        $this->head = $login->stats->head;
        $this->body = $login->stats->body;
        $this->feet = $login->stats->feet;

        $this->hat_array = explode(",", $login->stats->hat_array);
        $this->head_array = explode(",", $login->stats->head_array);
        $this->body_array = explode(",", $login->stats->body_array);
        $this->feet_array = explode(",", $login->stats->feet_array);

        if (isset($login->epic_upgrades->epic_hats)) {
            $this->epic_hat_array = $this->safe_explode($login->epic_upgrades->epic_hats);
            $this->epic_head_array = $this->safe_explode($login->epic_upgrades->epic_heads);
            $this->epic_body_array = $this->safe_explode($login->epic_upgrades->epic_bodies);
            $this->epic_feet_array = $this->safe_explode($login->epic_upgrades->epic_feet);
        }

        $this->speed = $login->stats->speed;
        $this->acceleration = $login->stats->acceleration;
        $this->jumping = $login->stats->jumping;

        $this->friends_array = $login->friends;
        $this->ignored_array = $login->ignored;

        $this->domain = $login->login->domain;
        $this->version = $login->login->version;

        $this->rt_used = $login->rt_used;
        $this->rt_available = $login->rt_available;
        $this->exp_today = $this->start_exp_today = $login->exp_today;
        $this->status = $login->status;

        $socket->player = $this;
        $this->active_rank = $this->rank + $this->rt_used;

        global $player_array;
        global $max_players;

        if ((count($player_array) > $max_players && $this->group < 2) || (count($player_array) > ($max_players-10) && $this->group == 0)) {
            $this->write('loginFailure`');
            $this->write('message`Sorry, this server is full. Try back later.');
            $this->remove();
        } else {
            $player_array[$this->user_id] = $this;
        }

        $this->award_kong_hat();
        $this->apply_temp_items();
        $this->verify_stats();
        $this->verify_parts();
    }


    private function safe_explode($str_arr)
    {
        if (isset($str_arr) && strlen($str_arr) > 0) {
            $arr = explode(',', $str_arr);
        } else {
            $arr = array();
        }
        return $arr;
    }


    private function apply_temp_items()
    {
        $temp_items = \pr2\TemporaryItems::getItems($this->user_id, $this->guild_id);
        foreach ($temp_items as $item) {
            // $this->gain_part('e'.ucfirst($item->type), $item->part_id);
            $this->set_part($item->type, $item->part_id, true);
        }
    }


    public function inc_exp($exp)
    {
        $max_rank = RankupCalculator::get_exp_required($this->active_rank+1);
        $this->write('setExpGain`'.$this->exp_points.'`'.($this->exp_points+$exp).'`'.$max_rank);
        $this->exp_points += $exp;
        $this->exp_today += $exp;

        //rank up
        if ($this->exp_points >= $max_rank) {
            $this->rank++;
            $this->active_rank++;
            $this->exp_points = ($this->exp_points - $max_rank);
            $this->write('setRank`'.$this->active_rank);
        }
    }


    public function inc_rank($inc)
    {
        $this->rank += $inc;
        if ($this->rank < 0) {
            $this->rank = 0;
        }
        $this->active_rank = $this->rank + $this->rt_used;
    }


    public function maybe_save()
    {
        $time = time();
        if ($time - $this->last_save_time > 60 * 2) {
            $this->last_save_time = $time;
            $this->save_info();
        }
    }


    public function use_rank_token()
    {
        if ($this->rt_used < $this->rt_available) {
            $this->rt_used++;
        }
        $this->active_rank = $this->rank + $this->rt_used;
    }


    public function unuse_rank_token()
    {
        if ($this->rt_used > 0) {
            $this->rt_used--;
        }
        $this->active_rank = $this->rank + $this->rt_used;

        if ($this->speed + $this->acceleration + $this->jumping > 150 + $this->active_rank) {
            $this->speed--;
        }
        $this->verify_stats();
    }


    public function send_chat($chat_message)
    {

        // globals and variables
        global $guild_id, $guild_owner, $player_array, $port, $server_name, $server_id, $server_expire_time, $uptime, $pdo;
        $admin_name = $this->name;
        $admin_id = $this->user_id;
        $ip = $this->ip;

        // sanity check: is the message more than 100 characters?
        if (strlen($chat_message) > 100) {
            $chat_message = substr($chat_message, 0, 100);
        }

        // find what room the player is in
        if (isset($this->chat_room) && !isset($this->game_room)) {
            $room_type = "c"; // c for chat
            $player_room = $this->chat_room;
        } elseif (isset($this->game_room) && !isset($this->chat_room)) {
            $room_type = "g"; // g for game
            $player_room = $this->game_room;
        } // this should never happen
        elseif (isset($this->chat_room) && isset($this->game_room)) {
            $room_type = "b"; // b for both
        } // this also should never happen
        else {
            $room_type = "n"; // n for none
        }

        //special text emotes
        if ($room_type == 'c') {
            $chat_message = str_replace(":shrug:", "¯\_(ツ)_/¯", $chat_message);
            $chat_message = str_replace(":lenny:", "( ͡° ͜ʖ ͡°)", $chat_message);
        }

        // html killer for systemChat
        $safe_chat_message = htmlspecialchars($chat_message);

        // switch for text effects
        switch ($chat_message) {
            case '/b':
                $chat_effect = 'bold';
                $chat_effect_tag = '<b>';
                break;
            case '/i':
                $chat_effect = 'italicized';
                $chat_effect_tag = '<i>';
                break;
            case '/u':
                $chat_effect = 'underlined';
                $chat_effect_tag = '<u>';
                break;
            case '/li':
                $chat_effect = 'bulleted';
                $chat_effect_tag = '<li>';
                break;
            default:
                $chat_effect = null;
                $chat_effect_tag = null;
                break;
        }

        // make sure they're in exactly one valid room
        if ($room_type != 'n' && $room_type != 'b' && isset($player_room)) {
            // --- chat commands --- \\
            // tournament mode
            if (strpos($chat_message, '/t ') === 0 || strpos($chat_message, '/tournament ') === 0 || $chat_message == '/t' || $chat_message == '/tournament') {
                // if server owner, allow them to do server owner things
                if ($this->server_owner == true) {
                    // help
                    if ($chat_message == '/t help' || $chat_message == '/t' || $chat_message == '/tournament') {
                        $this->write('systemChat`Welcome to tournament mode!<br><br>To enable a tournament, use /t followed by a hat name and stat values for the desired speed, acceleration, and jumping of the tournament.<br><br>Example: /t none 65 65 65<br>Hat: None<br>Speed: 65<br>Accel: 65<br>Jump: 65<br><br>To turn off tournament mode, type /t off. To find out whether tournament mode is on or off, type /t status.');
                    } // status
                    elseif ($chat_message == '/t status') {
                        tournament_status($this);
                    } // tournament mode
                    else {
                        try {
                            //handle exceptions
                            $caught_exception = false;

                            // attempt to start a tournament using the specified parameters
                            issue_tournament($safe_chat_message);
                        } catch (Exception $e) {
                            $caught_exception = true;
                            $err = $e->getMessage();
                            $err_supl = " Make sure you typed everything correctly! For help with tournaments, type /t help.";
                            $this->write('systemChat`Error: ' . $err . $err_supl);
                        }

                        // if an error was not encountered, announce the tournament to the chatroom
                        if (!$caught_exception) {
                            announce_tournament($player_room);
                        }
                    }
                } // if not the guild owner, limit their ability to checking the status of a tournament only
                else {
                    // status
                    if ($chat_message == '/t status' || $chat_message == '/t' || $chat_message == '/tournament') {
                        tournament_status($this);
                    } // tell them how to get the status
                    else {
                        $this->write('systemChat`To find out whether tournament mode is on or off, type /t status.');
                    }
                }
            } // chat effects
            elseif (!is_null($chat_effect) && $this->group >= 2 && ($this->temp_mod == false || $this->server_owner == true)) {
                if ($room_type == 'c') {
                    $player_room->send_chat('systemChat`' . $chat_effect_tag . $this->name . ' has temporarily activated ' . $chat_effect . ' chat!');
                } else {
                    $this->write('systemChat`This command cannot be used in levels.');
                }
            } // chat announcements
            elseif (strpos($chat_message, '/a ') === 0 && $this->group >= 2 && ($this->temp_mod == false || $this->server_owner == true)) {
                $announcement = trim(substr($chat_message, 3));
                $safe_announcement = htmlspecialchars($announcement); // html killer
                $announce_length = strlen($safe_announcement);

                if ($announce_length >= 1) {
                    $player_room->send_chat('systemChat`Chatroom Announcement from '.$this->name.': ' . $safe_announcement);
                } else {
                    $this->write('systemChat`Your announcement must be at least 1 character.');
                }
            } // "give" command
            elseif (strpos($chat_message, '/give ') === 0 && $this->group >= 2 && ($this->temp_mod == false || $this->server_owner == true)) {
                $give_this = trim(substr($chat_message, 6));
                $safe_give_this = htmlspecialchars($give_this); // html killer
                $give_this_length = strlen($safe_give_this);

                if ($give_this_length >= 1) {
                    $player_room->send_chat('systemChat`'.$this->name.' has given ' . $safe_give_this);
                } else {
                    $this->write('systemChat`The thing you\'re giving must be at least 1 character.');
                }
            } // "promote" command
            elseif (strpos($chat_message, '/promote ') === 0 && $this->group >= 3 && $this->server_owner == false) {
                $promote_this = trim(substr($chat_message, 9));
                $safe_promote_this = htmlspecialchars($promote_this); // html killer
                $promote_this_length = strlen($safe_promote_this);

                if ($promote_this_length >= 1) {
                    $player_room->send_chat('systemChat`'.$this->name.' has promoted ' . $safe_promote_this);
                } else {
                    $this->write('systemChat`The thing you\'re promoting must be at least 1 character.');
                }
            } // population command
            elseif ($chat_message == '/pop' || $chat_message == '/population') {
                $pop_counted = count($player_array); // count players
                $pop_singular = array("is", "user"); // language for 1 player
                $pop_plural = array("are", "users"); // language for multiple players

                if ($pop_counted === 1) {
                    $pop_lang = $pop_singular; // if there is only one player, associate the singular language with the called variable
                } else {
                    $pop_lang = $pop_plural; // if there is more than one player, associate the plural language with the called variable
                }

                $this->write('systemChat`There '.$pop_lang[0].' currently '.$pop_counted.' '.$pop_lang[1].' playing on this server.');
            } // clear command
            elseif (($chat_message == '/clear' || $chat_message == '/cls') && $this->group >= 2 && ($this->temp_mod == false || $this->server_owner == true)) {
                if ($player_room == $this->chat_room) {
                    $player_room->clear();
                } else {
                    $this->write('systemChat`This command cannot be used in levels.');
                }
            } // debug command for admins
            elseif (($chat_message == '/debug' || strpos($chat_message, '/debug ') === 0) && $this->group >= 3 && $this->server_owner == false) {
                $is_ps = 'no';
                if ($guild_id != '0') {
                    $is_ps = 'yes';
                }

                if ($chat_message == '/debug help') {
                    $this->write("systemChat`Acceptable Arguments:<br><br>- help<br>- player *name*<br>- restart_server<br>- server");
                } elseif ($chat_message == '/debug restart_server') {
                    $this->write("message`chat_message: $chat_message<br>admin_name: $admin_name<br>admin_id: $admin_id<br>ip: $ip<br>server_name: $server_name<br>server_id: $server_id<br>port: $port");
                } elseif ($chat_message == '/debug server') {
                    $this->write("message`chat_message: $chat_message<br>port: $port<br>server_name: $server_name<br>server_id: $server_id<br>uptime: $uptime<br>private_server: $is_ps<br>server_guild: $guild_id<br>server_owner: $guild_owner<br>server_expire_time: $server_expire_time");
                } elseif (strpos($chat_message, '/debug player ') === 0) {
                    $player_name = trim(substr($chat_message, 14));
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
                        if ($player->temp_mod === true) {
                            $ptemp = 'yes';
                        } else {
                            $ptemp = 'no';
                        }
                        if ($player->server_owner === true) {
                            $pso = 'yes';
                        } else {
                            $pso = 'no';
                        }

                        $this->write(
                            "message`"
                            ."chat_message: $chat_message<br>"
                            ."ip: $pip<br>"
                            ."name: $pname | user_id: $puid<br>"
                            ."status: $pstatus<br>"
                            ."group: $pgroup | temp_mod: $ptemp | server_owner: $pso<br>"
                            ."guild_id: $pguild<br>"
                            ."active_rank: $parank | rank (no rt): $prank | rt_used: $prtused | rt_avail: $prtavail<br>"
                            ."exp_today: $pexp2day | exp_points: $pexppoints<br>"
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
                    $this->write("systemChat`Enter an argument to get the data you want. For a list of acceptable arguments, type /debug help.");
                }
            } // restart server command for admins
            elseif ($chat_message == '/restart_server' && $this->group >= 3 && ($this->server_owner == false || $guild_id == 183)) {
                if ($room_type == 'c') {
                    if ($this->restart_warned == false) {
                        $this->restart_warned = true;
                        $this->write('systemChat`WARNING: You just typed the server restart command. If you choose to proceed, this action will disconnect EVERY player on this server. Are you sure you want to disconnect ALL players and restart the server? If so, type the command again.');
                    } else if ($this->restart_warned == true) {
                        admin_action_insert($pdo, $admin_id, "$admin_name restarted $server_name from $ip.", $admin_id, $ip);
                        shutdown_server();
                    }
                } else {
                    $this->write('systemChat`This command cannot be used in levels.');
                }
            } // time left in a private server command
            elseif ($chat_message == '/timeleft' && $this->server_owner == true) {
                if ($server_id > 10) {
                    $this->write("systemChat`Your server will expire on $server_expire_time. To extend your time, buy either Private Server 1 or Private Server 30 from the Vault of Magics.");
                } else {
                    $this->write("systemChat`This is not a private server.");
                }
            } // be awesome command
            elseif ($chat_message == '/be_awesome' || $chat_message == '/beawesome') {
                $this->write("message`<b>You're awesome!</b>");
            } // kick command
            elseif (strpos($chat_message, '/kick ') === 0 && $this->group >= 2 && ($this->temp_mod === false || $this->server_owner == true)) {
                $kicked_name = trim(substr($chat_message, 6));
                client_kick($this->socket, $kicked_name);
            } // disconnect command
            elseif ((strpos($chat_message, '/dc ') === 0 || strpos($chat_message, '/disconnect ') === 0) && $this->group >= 2 && ($this->temp_mod === false || $this->server_owner == true)) {
                $dc_name = trim(substr($chat_message, 12)); // for /disconnect
                if (strpos($chat_message, '/dc ') === 0) {
                    $dc_name = trim(substr($chat_message, 4)); // for /dc
                }
                $dc_player = name_to_player($dc_name);
                $safe_dc_name = htmlspecialchars($dc_name); // make the name safe to echo back to the user

                // permission checks
                if (isset($dc_player) && ($dc_player->group < 2 || $this->server_owner == true || $dc_player->temp_mod == true)) {
                    $mod_id = $this->user_id;
                    $mod_name = $this->name;
                    $mod_ip = $this->ip;

                    // do it
                    $dc_player->remove();

                    // if they're an actual mod, log it
                    if ($this->server_owner == false || $mod_id == self::FRED) {
                        mod_action_insert($pdo, $mod_id, "$mod_name disconnected $dc_name from $server_name from $mod_ip.", $mod_id, $mod_ip);
                    }

                    // tell the world
                    $this->write("message`$safe_dc_name has been disconnected."); // tell the disconnector
                } elseif (isset($dc_player) && ($dc_player->group > 2 || $this->server_owner == false)) {
                    $this->write("message`Error: You lack the power to disconnect $safe_dc_name.");
                } else {
                    $this->write("message`Error: Could not find a user with the name \"$safe_dc_name\" on this server."); // they're not online or don't exist
                }
            } // hh status for everyone, activate for admins, deactivate for admins and server owners
            elseif (($chat_message == '/hh' || strpos($chat_message, '/hh ') === 0)) {
                $args = explode(' ', $chat_message);
                array_shift($args);

                if ($chat_message == '/hh' || $args[0] == 'status') {
                    $hh_timeleft = HappyHour::timeLeft();
                    if ($hh_timeleft != false) {
                        $this->write('systemChat`There is currently a Happy Hour on this server! It will expire in ' . format_duration($hh_timeleft) . '.');
                    } else {
                        $this->write('systemChat`There is not currently a Happy Hour on this server.');
                    }
                } elseif ($args[0] == 'help') {
                    $hhmsg_admin = '';
                    $hhmsg_server_owner = '';
                    $hhmsg_warning = 'WARNING: This will remove all stacked Happy Hours bought by all users on this server from the Vault of Magics, as well as ending the current one.';
                    if ($this->group >= 3 && $this->server_owner == false) {
                        $hhmsg_admin = "To activate a Happy Hour, type /hh activate. To deactivate the current Happy Hour, type /hh deactivate. $hhmsg_warning";
                    } elseif ($this->group >= 3 && $this->server_owner == true) {
                        $hhmsg_server_owner = "To deactivate the current Happy Hour, type /hh deactivate. $hhmsg_warning";
                    }
                    $this->write('systemChat`To find out if a Happy Hour is active and when it expires, type /hh status. '.$hhmsg_admin.$hhmsg_server_owner);
                } elseif ($args[0] == 'activate' && $this->group >=3 && $this->server_owner == false) {
                    if (HappyHour::isActive() != true && pr2_server::$tournament == false) {
                        if (!isset($args[1])) {
                            HappyHour::activate();
                        } else {
                            $args[1] = (int) $args[1];
                            if ($args[1] > 3600) {
                                $args[1] = 3600;
                            }
                            HappyHour::activate($args[1]);
                        }
                        $player_room->send_chat('systemChat`'.htmlspecialchars($this->name).' just triggered a Happy Hour!');
                    } elseif (pr2_server::$tournament == true) {
                        $this->write('systemChat`You can\'t activate a Happy Hour on a server with tournament mode enabled. Disable tournament mode and try again.');
                    } else {
                        $hh_timeleft = HappyHour::timeLeft();
                        $this->write('systemChat`There is already a Happy Hour on this server. It will expire in ' . format_duration($hh_timeleft) . '.');
                    }
                } elseif ($args[0] == 'deactivate' && $this->group >= 3) {
                    if (!$this->hh_warned) {
                        $this->hh_warned = true;
                        $this->write("systemChat`WARNING: This will remove ALL stacked Happy Hours bought by ALL users on this server from the Vault of Magics, as well as ending the current one. If you're sure you want to do this, type the command again.");
                    } elseif (HappyHour::isActive() && $this->hh_warned) {
                        HappyHour::deactivate();
                        $player_room->send_chat('systemChat`' . htmlspecialchars($this->name) . ' just ended the current Happy Hour.');
                    } else {
                        $this->write('systemChat`There isn\'t an active Happy Hour right now.');
                    }
                } else {
                    $this->write('systemChat`Error: Invalid argument specified. Type /hh help for more information.');
                }
            } // server mod command for server owners
            elseif (($chat_message == '/mod' || strpos($chat_message, '/mod ') === 0) && $this->group >= 3 && $this->server_owner == true && $this->user_id != self::FRED) {
                if ($chat_message == '/mod help') {
                    $this->write('systemChat`To promote someone to a server moderator, type "/mod promote" followed by their username. They will be a server moderator until they log out or are demoted. To demote an existing server moderator, type "/mod demote" followed by their username.');
                } elseif (strpos($chat_message, '/mod promote ') === 0 || strpos($chat_message, '/mod demote ') === 0) {
                    if (strpos($chat_message, '/mod promote ') === 0) {
                        $action = 'promote';
                        $to_name = trim(substr($chat_message, 13));
                    } elseif (strpos($chat_message, '/mod demote ') === 0) {
                        $action = 'demote';
                        $to_name = trim(substr($chat_message, 12));
                    }
                    $target = name_to_player($to_name);
                    $owner = $this;

                    // do the appropriate action
                    if ($action == 'promote') {
                        promote_server_mod($to_name, $owner, $target);
                    } elseif ($action == 'demote') {
                        demote_server_mod($to_name, $owner, $target);
                    }
                } else {
                    $this->write("Invalid action. For more information on how to use this command, type /mod help.");
                }
            } // rules command
            elseif ($chat_message == '/rules') {
                $message = 'systemChat`The PR2 rules can be found here: https://jiggmin2.com/forums/showthread.php?tid=385.';
                if ($guild_id != 0) {
                    $message .= ' Since this is a private server, your guild owner may have different rules for the chatrooms and the server. Check with them if you\'re unsure.';
                }
                $this->write($message);
            } // help command
            elseif ($chat_message == '/help' || $chat_message == '/commands' || $chat_message == '/?' || $chat_message == '/') {
                $mod = '';
                $effects = '';
                $admin = '';
                $server_owner = '';

                if ($room_type == 'g') {
                    $this->write('systemChat`To get a list of commands that can be used in the chatroom, go to the chat tab in the lobby and type /help.');
                } else {
                    if ($this->group >= 2) {
                        if ($this->temp_mod === false) {
                            $mod = '<br>Moderator:<br>- /a (Announcement)<br>- /give *text*<br>- /kick *name*<br>- /disconnect *name*<br>- /clear';
                            $effects = '<br>Chat Effects:<br>- /b (Bold)<br>- /i (Italics)<br>- /u (Underlined)<br>- /li (Bulleted)';
                        }
                        if ($this->group >= 3 && $this->server_owner == false) {
                            $admin = '<br>Admin:<br>- /promote *message*<br>- /restart_server<br>- /debug *arg*<br>- /hh help';
                        }
                        if ($this->server_owner == true) {
                            $server_owner = '<br>Server Owner:<br>- /timeleft<br>- /mod help<br>- /hh help<br>- /t (Tournament)<br>For more information on tournaments, use /t help.';
                        }
                    }
                    $this->write('systemChat`PR2 Chat Commands:<br>- /rules<br>- /view *player*<br>- /guild *guild name*<br>- /hint (Artifact)<br>- /hh status<br>- /t status<br>- /population<br>- /beawesome'.$mod.$effects.$admin.$server_owner);
                }
            } // --- send chat message --- \\
            else {
                // guest check
                if ($this->group <= 0 || $this->guest == true) {
                    $this->write("systemChat`Sorries, guests can't send chat messages.");
                } // rank 3 check
                elseif ($this->active_rank < 3 && $this->group < 2) {
                    $this->write('systemChat`Sorries, you must be rank 3 or above to chat.');
                } // chat ban check (warnings, auto-warn)
                elseif ($this->chat_ban > time() && ($this->group < 2 || $this->temp_mod === true)) {
                    $chat_ban_seconds = $this->chat_ban - time();
                    $this->write("systemChat`You have been temporarily banned from the chat. The ban will be lifted in $chat_ban_seconds seconds.");
                } // spam check
                elseif ($this->get_chat_count() > 6 && ($this->group < 2 || $this->temp_mod === true)) {
                    $this->chat_ban = time() + 60;
                    $this->write('systemChat`Slow down a bit, yo.');
                }
                // illegal character in username/message check
                elseif (strpos($this->name, '`') !== false || strpos($chat_message, '`') !== false) {
                    $this->write('message`Error: Illegal character detected.');
                } // if caught by NOTHING above, send the message
                else {
                    $message = 'chat`'.$this->name.'`'.$this->group.'`'.$chat_message;
                    $this->chat_count++;
                    $this->chat_time = time();
                    $player_room->send_chat($message, $this->user_id);
                }
            }
        } // this should never happen
        elseif ($room_type == 'b') {
            $this->write("message`Error: You can't be in two places at once!");
        } // this should also never happen
        elseif ($room_type == 'n') {
            $this->write("message`Error: You don't seem to be in a valid chatroom.");
        } // aaaaand this most certainly will never happen
        else {
            $this->write("message`Error: The server encountered an error when trying to determine what chatroom you're in. Try rejoining the chatroom and sending your message again. If this error persists, contact a member of the PR2 Staff Team.");
        }
    }



    private function get_chat_count()
    {
        $seconds = time() - $this->chat_time;
        $this->chat_count -= $seconds / 2;
        if ($this->chat_count < 0) {
            $this->chat_count = 0;
        }
        return $this->chat_count;
    }


    public function is_ignored_id($id)
    {
        if (array_search($id, $this->ignored_array) === false) {
            return false;
        } else {
            return true;
        }
    }


    public function send_customize_info()
    {
        $this->socket->write(
            'setCustomizeInfo'
            .'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
            .'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
            .'`'.join(',', $this->get_full_parts('hat'))
            .'`'.join(',', $this->get_full_parts('head'))
            .'`'.join(',', $this->get_full_parts('body'))
            .'`'.join(',', $this->get_full_parts('feet'))
            .'`'.$this->get_real_stat_str()
            .'`'.$this->active_rank
            .'`'.$this->rt_used.'`'.$this->rt_available
            .'`'.$this->hat_color_2.'`'.$this->head_color_2.'`'.$this->body_color_2.'`'.$this->feet_color_2
            .'`'.join(',', $this->get_full_parts('eHat'))
            .'`'.join(',', $this->get_full_parts('eHead'))
            .'`'.join(',', $this->get_full_parts('eBody'))
            .'`'.join(',', $this->get_full_parts('eFeet'))
        );
    }


    public function get_remote_info()
    {
        return 'createRemoteCharacter'
        .'`'.$this->temp_id.'`'.$this->name
        .'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
        .'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
        .'`'.$this->get_second_color('hat', $this->hat).'`'.$this->get_second_color('head', $this->head).'`'.$this->get_second_color('body', $this->body).'`'.$this->get_second_color('feet', $this->feet);
    }


    public function get_local_info()
    {
        return 'createLocalCharacter'
        .'`'.$this->temp_id
        .'`'.$this->get_stat_str()
        .'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
        .'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
        .'`'.$this->get_second_color('hat', $this->hat).'`'.$this->get_second_color('head', $this->head).'`'.$this->get_second_color('body', $this->body).'`'.$this->get_second_color('feet', $this->feet);
    }


    public function get_second_color($type, $id)
    {
        if ($type === 'hat') {
            $color = $this->hat_color_2;
            $epic_arr = $this->epic_hat_array;
        } elseif ($type === 'head') {
            $color = $this->head_color_2;
            $epic_arr = $this->epic_head_array;
        } elseif ($type === 'body') {
            $color = $this->body_color_2;
            $epic_arr = $this->epic_body_array;
        } elseif ($type === 'feet') {
            $color = $this->feet_color_2;
            $epic_arr = $this->epic_feet_array;
        }

        if (array_search($id, $epic_arr) === false && array_search('*', $epic_arr) === false) {
            $color = -1;
        }

        return( $color );
    }


    public function award_kong_hat()
    {
        if (strpos($this->domain, 'kongregate.com') !== false) {
            $added = $this->gain_part('hat', 3, true);
            if ($added) {
                $this->hat_color = 10027008;
            }
        }
    }


    public function award_kong_outfit()
    {
        $this->gain_part('head', 20, true);
        $this->gain_part('body', 17, true);
        $this->gain_part('feet', 16, true);
    }



    public function gain_part($type, $id, $autoset = false)
    {
        if ($type === 'hat') {
            $arr = &$this->hat_array;
        } elseif ($type === 'head') {
            $arr = &$this->head_array;
        } elseif ($type === 'body') {
            $arr = &$this->body_array;
        } elseif ($type === 'feet') {
            $arr = &$this->feet_array;
        } elseif ($type === 'eHat') {
            $arr = &$this->epic_hat_array;
        } elseif ($type === 'eHead') {
            $arr = &$this->epic_head_array;
        } elseif ($type === 'eBody') {
            $arr = &$this->epic_body_array;
        } elseif ($type === 'eFeet') {
            $arr = &$this->epic_feet_array;
        } else {
            echo("Player::gain_part - unknown part type: $type \n");
            return false;
        }

        if (isset($arr) && array_search($id, $arr) === false) {
            array_push($arr, $id);
            if ($autoset) {
                $this->set_part($type, $id);
            }
            return true;
        } else {
            return false;
        }
    }



    public function set_part($type, $id)
    {
        if (strpos($type, 'e') === 0) {
            $type = substr($type, 1);
            $type = strtolower($type);
        }

        if ($type == 'hat') {
            $this->hat = $id;
        } elseif ($type == 'head') {
            $this->head = $id;
        } elseif ($type == 'body') {
            $this->body = $id;
        } elseif ($type == 'feet') {
            $this->feet = $id;
        }
    }



    private function get_stat_str()
    {
        if (HappyHour::isActive()) {
            $speed = 100;
            $accel = 100;
            $jump = 100;
        } elseif (pr2_server::$tournament) {
            $speed = pr2_server::$tournament_speed;
            $accel = pr2_server::$tournament_acceleration;
            $jump = pr2_server::$tournament_jumping;
        } else {
            $speed = $this->speed;
            $accel = $this->acceleration;
            $jump = $this->jumping;
        }
        if ($this->super_booster) {
            $speed += 10;
            $accel += 10;
            $jump += 10;
        }
        $str = "$speed`$accel`$jump";
        return $str;
    }


    private function get_real_stat_str()
    {
        $speed = $this->speed;
        $accel = $this->acceleration;
        $jump = $this->jumping;
        $str = "$speed`$accel`$jump";
        return $str;
    }


    public function set_customize_info($data)
    {
        list($hat_color, $head_color, $body_color, $feet_color,
        $hat_color_2, $head_color_2, $body_color_2, $feet_color_2,
        $hat, $head, $body, $feet,
        $speed, $acceleration, $jumping) = explode('`', $data);

        $this->hat_color = $hat_color;
        $this->head_color = $head_color;
        $this->body_color = $body_color;
        $this->feet_color = $feet_color;

        if ($hat_color_2 != -1) {
            $this->hat_color_2 = $hat_color_2;
        }
        if ($head_color_2 != -1) {
            $this->head_color_2 = $head_color_2;
        }
        if ($body_color_2 != -1) {
            $this->body_color_2 = $body_color_2;
        }
        if ($feet_color_2 != -1) {
            $this->feet_color_2 = $feet_color_2;
        }

        $this->hat = $hat;
        $this->head = $head;
        $this->body = $body;
        $this->feet = $feet;

        if ($speed + $acceleration + $jumping <= 150 + $this->active_rank) {
            $this->speed = $speed;
            $this->acceleration = $acceleration;
            $this->jumping = $jumping;
        }

        $this->verify_parts();
        $this->verify_stats();
        $this->maybe_save();
    }



    private function verify_stats()
    {
        if ($this->speed < 0) {
            $this->speed = 0;
        }
        if ($this->acceleration < 0) {
            $this->acceleration = 0;
        }
        if ($this->jumping < 0) {
            $this->jumping = 0;
        }

        if ($this->speed > 100) {
            $this->speed = 100;
        }
        if ($this->acceleration > 100) {
            $this->acceleration = 100;
        }
        if ($this->jumping > 100) {
            $this->jumping = 100;
        }

        if ($this->speed + $this->acceleration + $this->jumping > 150 + $this->active_rank) {
            $this->speed = 50;
            $this->acceleration = 50;
            $this->jumping = 50;
        }
    }



    private function verify_parts($strict = false)
    {
        $this->verify_part($strict, 'hat');
        $this->verify_part($strict, 'head');
        $this->verify_part($strict, 'body');
        $this->verify_part($strict, 'feet');
    }



    private function verify_part($strict, $type)
    {
        $eType = 'e'.ucfirst($type);
        $part = $this->{$type};

        if ($strict) {
            $parts_available = $this->get_owned_parts($type);
            $epic_parts_available = $this->get_owned_parts($eType);
        } else {
            $parts_available = $this->get_full_parts($type);
            $epic_parts_available = $this->get_full_parts($eType);
        }

        if (array_search($part, $parts_available) === false) {
            $part = $parts_available[0];
            $this->{$type} = $part;
        }
    }


    private function get_owned_parts($type)
    {
        if (substr($type, 0, 1) == 'e') {
            $arr = $this->{'epic_'.strtolower(substr($type, 1)).'_array'};
        } else {
            $arr = $this->{$type.'_array'};
        }
        return $arr;
    }


    private function get_full_parts($type)
    {
        $perm = $this->get_owned_parts($type);
        $temp = \pr2\TemporaryItems::getParts($type, $this->user_id, $this->guild_id);
        $full = array_merge($perm, $temp);
        return $full;
    }


    public function write($str)
    {
        if (isset($this->socket)) {
            $this->socket->write($str);
        }
    }



    public function wearing_hat($hat_num)
    {
        $wearing = false;
        foreach ($this->worn_hat_array as $hat) {
            if ($hat->num === $hat_num) {
                $wearing = true;
            }
        }
        return $wearing;
    }



    public function become_temp_mod()
    {
        $this->group = 2;
        $this->temp_mod = true;
        $this->write('becomeTempMod`');
    }



    public function become_server_owner()
    {
        $this->write('becomeTempMod`'); // to pop up the temp mod menu for server owners
        $this->server_owner = true;
        $this->group = 3;
        $this->write('setGroup`3');
        if ($this->user_id != self::FRED) {
            $this->write("message`Welcome to your private server! You have admin privileges here. To promote server mods, type /mod promote *player name here* in the chat. They'll remain modded until they log out.<br><br>For more information about what commands you can use, type /help in the chat.");
        }
    }



    public function become_guest()
    {
        $this->guest = true;
        $this->group = 1;
        $this->write('setGroup`1');
        $this->write("message`Welcome to Platform Racing 2!<br><br>You're a guest, which means you'll have limited privileges. To gain full functionality, log out and create your own account.<br><br>Thanks for playing, I hope you enjoy!<br>-Jacob");
    }



    public function save_info()
    {
        global $server_id, $pdo;

        // make sure none of the part values are blank to avoid server crashes
        if (is_empty($this->hat, false)) {
            $this->gain_part('hat', 1, true);
        }
        if (is_empty($this->head, false)) {
            $this->gain_part('head', 1, true);
        }
        if (is_empty($this->body, false)) {
            $this->gain_part('body', 1, true);
        }
        if (is_empty($this->feet, false)) {
            $this->gain_part('feet', 1, true);
        }

        // auto removing some hat?
        $index = array_search(27, $this->hat_array);
        if ($index !== false) {
            array_splice($this->hat_array, $index, 1);
        }

        $rank = $this->rank;
        $exp_points = $this->exp_points;

        $hat_color = $this->hat_color;
        $head_color = $this->head_color;
        $body_color = $this->body_color;
        $feet_color = $this->feet_color;

        $hat_color_2 = $this->hat_color_2;
        $head_color_2 = $this->head_color_2;
        $body_color_2 = $this->body_color_2;
        $feet_color_2 = $this->feet_color_2;

        $hat = $this->hat;
        $head = $this->head;
        $body = $this->body;
        $feet = $this->feet;

        $hat_array = join(',', $this->hat_array);
        $head_array = join(',', $this->head_array);
        $body_array = join(',', $this->body_array);
        $feet_array = join(',', $this->feet_array);

        $epic_hat_array = join(',', $this->epic_hat_array);
        $epic_head_array = join(',', $this->epic_head_array);
        $epic_body_array = join(',', $this->epic_body_array);
        $epic_feet_array = join(',', $this->epic_feet_array);

        $speed = $this->speed;
        $acceleration = $this->acceleration;
        $jumping = $this->jumping;

        $status = $this->status;
        $e_server_id = $server_id;

        $rt_used = $this->rt_used;
        $ip = $this->ip;
        $tot_exp_gained = $this->exp_today - $this->start_exp_today;

        if ($status == 'offline') {
            $e_server_id = 0;
        }

        if ($this->group == 0 || $this->guest === true) {
            $rank = 0;
            $exp_points = 0;
            $hat_array = '1';
            $head_array = '1,2,3,4,5,6,7,8,9';
            $body_array = '1,2,3,4,5,6,7,8,9';
            $feet_array = '1,2,3,4,5,6,7,8,9';
            $epic_hat_array = '';
            $epic_head_array = '';
            $epic_body_array = '';
            $epic_feet_array = '';
            $hat = 1;
            $head = 1;
            $body = 1;
            $feet = 1;
            $rt_used = 0;
            $speed = 50;
            $acceleration = 50;
            $jumping = 50;
        }

        pr2_update(
            $pdo,
            $this->user_id, $rank, $exp_points,
            $hat_color, $head_color, $body_color, $feet_color,
            $hat_color_2, $head_color_2, $body_color_2, $feet_color_2,
            $hat, $head, $body, $feet,
            $hat_array, $head_array, $body_array, $feet_array,
            $speed, $acceleration, $jumping
        );

        epic_upgrades_upsert($pdo, $this->user_id, $epic_hat_array, $epic_head_array, $epic_body_array, $epic_feet_array);
        user_update_status($pdo, $this->user_id, $status, $e_server_id);
        rank_token_update($pdo, $this->user_id, $rt_used);
        exp_today_add($pdo, 'id-' . $this->user_id, $tot_exp_gained);
        exp_today_add($pdo, 'ip-' . $ip, $tot_exp_gained);
    }



    public function remove()
    {
        global $player_array;

        unset($player_array[$this->user_id]);

        //make sure the socket is nice and dead
        if (is_object($this->socket)) {
            unset($this->socket->player);
            if ($this->socket->disconnected === false) {
                $this->socket->close();
                $this->socket->on_disconnect();
            }
        }

        //get out of whatever you're in
        if (isset($this->right_room)) {
            $this->right_room->remove_player($this);
        }
        if (isset($this->chat_room)) {
            $this->chat_room->remove_player($this);
        }
        if (isset($this->game_room)) {
            $this->game_room->remove_player($this);
        }
        if (isset($this->course_box)) {
            $this->course_box->clear_slot($this);
        }

        //save info
        $this->status = "offline";
        $this->verify_stats();
        $this->verify_parts(true);
        $this->save_info();

        //delete
        $this->socket = null;
        $this->user_id = null;
        $this->name = null;
        $this->rank = null;
        $this->active_rank = null;
        $this->exp_points = null;
        $this->group = null;
        $this->guest = null;
        $this->hat_color = null;
        $this->head_color = null;
        $this->body_color = null;
        $this->feet_color = null;
        $this->hat = null;
        $this->head = null;
        $this->body = null;
        $this->feet = null;
        $this->hat_array = null;
        $this->head_array = null;
        $this->body_array = null;
        $this->feet_array = null;
        $this->epic_hat_array = null;
        $this->epic_head_array = null;
        $this->epic_body_array = null;
        $this->epic_feet_array = null;
        $this->speed = null;
        $this->acceleration = null;
        $this->jumping = null;
        $this->friends = null;
        $this->ignored = null;
        $this->url = null;
        $this->version = null;
        $this->last_action = null;
        $this->chat_count = null;
        $this->chat_time = null;
        $this->right_room = null;
        $this->chat_room = null;
        $this->game_room = null;
        $this->course_box = null;
        $this->confirmed = null;
        $this->slot = null;
        $this->temp_id = null;
        $this->pos_x = null;
        $this->pos_y = null;
        $this->worn_hat_array = null;
        $this->finished_race = null;
        $this->quit_race = null;
        $this->chat_ban = null;
        $this->domain = null;
        $this->temp_mod = null;
        $this->server_owner = null;
        $this->hh_warned = null;
        $this->restart_warned = null;
        $this->status = null;
    }
}
