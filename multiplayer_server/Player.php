<?php

namespace pr2\multi;

class Player
{
    public $socket;
    public $user_id;
    public $guild_id;

    public $name;
    public $guild_name;
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

    public $hh_speed;
    public $hh_acceleration;
    public $hh_jumping;

    public $following_array;
    public $friends_array;
    public $ignored_array;

    public $rt_used;
    public $rt_available;

    public $url = '';
    public $version = '0.0';

    public $last_exp_time;
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
    public $rot = 0;

    public $worn_hat_array = array();

    public $domain;
    public $ip;

    public $sban_id = 0;
    public $sban_exp_time = 0;

    public $special_user = false;
    public $temp_mod = false;
    public $trial_mod = false;
    public $server_owner = false;

    public $hh_warned = false;
    public $restart_warned = false;

    public $status = '';
    public $register_time;
    public $login_time;

    public $lives = 3;
    public $items_used = 0;
    public $super_booster = false;
    public $last_save_time = 0;


    public function __construct($socket, $login)
    {
        global $player_array, $max_players, $special_ids;

        $this->socket = $socket;
        $this->ip = $socket->ip;
        $this->last_save_time = time();

        $this->user_id = (int) $login->user->user_id;
        $this->name = $login->user->name;
        $this->group = (int) $login->user->power;
        $this->trial_mod = (bool) (int) $login->user->trial_mod;
        $this->guild_name = !empty($login->user->guild_name) ? $login->user->guild_name : '';
        $this->guild_id = (int) $login->user->guild;

        $this->rank = (int) $login->stats->rank;
        $this->exp_points = (int) $login->stats->exp_points;

        $this->hat_color = (int) $login->stats->hat_color;
        $this->head_color = (int) $login->stats->head_color;
        $this->body_color = (int) $login->stats->body_color;
        $this->feet_color = (int) $login->stats->feet_color;

        $this->hat_color_2 = (int) $login->stats->hat_color_2;
        $this->head_color_2 = (int) $login->stats->head_color_2;
        $this->body_color_2 = (int) $login->stats->body_color_2;
        $this->feet_color_2 = (int) $login->stats->feet_color_2;

        $this->hat = (int) $login->stats->hat;
        $this->head = (int) $login->stats->head;
        $this->body = (int) $login->stats->body;
        $this->feet = (int) $login->stats->feet;

        $this->hat_array = explode(",", $login->stats->hat_array);
        $this->head_array = explode(",", $login->stats->head_array);
        $this->body_array = explode(",", $login->stats->body_array);
        $this->feet_array = explode(",", $login->stats->feet_array);

        if (isset($login->epic_upgrades->epic_hats)) {
            $this->epic_hat_array = $this->safeExplode($login->epic_upgrades->epic_hats);
            $this->epic_head_array = $this->safeExplode($login->epic_upgrades->epic_heads);
            $this->epic_body_array = $this->safeExplode($login->epic_upgrades->epic_bodies);
            $this->epic_feet_array = $this->safeExplode($login->epic_upgrades->epic_feet);
        }

        $this->speed = $this->hh_speed = (int) $login->stats->speed;
        $this->acceleration = $this->hh_acceleration = (int) $login->stats->acceleration;
        $this->jumping = $this->hh_jumping = (int) $login->stats->jumping;

        $this->following_array = $login->following;
        $this->friends_array = $login->friends;
        $this->ignored_array = $login->ignored;

        $this->domain = $login->login->domain;
        $this->version = $login->login->build;

        // check for an active social ban
        if (!empty($login->user->sban_id)) {
            $this->sban_id = (int) $login->user->sban_id;
            $this->sban_exp_time = (int) $login->user->sban_exp_time;
        }

        $this->rt_used = (int) $login->rt_used;
        $this->rt_available = (int) $login->rt_available;
        $this->exp_today = (int) $this->start_exp_today = (int) $login->exp_today;
        $this->last_exp_time = time();
        $this->status = $login->status;
        $this->register_time = (int) $login->user->register_time;
        $this->login_time = time();

        $socket->player = $this;
        $this->active_rank = $this->rank + $this->rt_used;

        // final checks
        $pCount = count($player_array); // server full?
        if (($pCount > $max_players && $this->group < 2) || ($pCount > ($max_players - 10) && $this->group === 0)) {
            $this->write('loginFailure`');
            $this->write('message`Sorry, this server is full. Try back later.');
            $this->remove();
        } elseif ($this->active_rank > 100 && $this->user_id != FRED) { // check for a valid rank
            $this->write('loginFailure`');
            $this->write('message`Your rank is too high. Please choose a different account.');
            $this->remove();
        } else { // add to the player array
            $player_array[$this->user_id] = $this;
        }

        // if they're a trial, tell the client
        if ($this->group === 2 && $this->trial_mod) {
            $this->write("becomeTrialMod`");
        }

        // if they're special, tell the client
        if (in_array($this->user_id, $special_ids)) {
            $this->special_user = true;
            $this->write('becomeSpecialUser`');
        }

        // if they're the prizer, tell the client
        if (PR2SocketServer::$prizer_id === $this->user_id) {
            $this->write('becomePrizer`');
        }

        if (isset($player_array[$this->user_id])) {
            if ($login->login->award_kong) {
                $this->awardKongParts();
            }
            $this->applyTempItems();
            $this->verifyStats();
            $this->verifyParts();
            $this->write("wearingHat`$this->hat");
        }
    }

    public function getInfo()
    {
        $ret = new \stdClass();
        $ret->userId = $this->user_id;
        $ret->name = $this->name;
        $ret->status = $this->status;
        $ret->group = $this->group;
        $ret->temp_mod = $this->temp_mod;
        $ret->trial_mod = $this->trial_mod;
        $ret->guildId = $this->guild_id;
        $ret->guildName = $this->guild_name;
        $ret->rank = $this->active_rank;
        $ret->hats = count($this->hat_array) - 1;
        $ret->registerDate = (int) $this->register_time;
        $ret->loginDate = $this->socket->last_user_action;
        $ret->hat = $this->hat;
        $ret->head = $this->head;
        $ret->body = $this->body;
        $ret->feet = $this->feet;
        $ret->hatColor = $this->hat_color;
        $ret->headColor = $this->head_color;
        $ret->bodyColor = $this->body_color;
        $ret->feetColor = $this->feet_color;
        $ret->hatColor2 = $this->getSecondColor('hat', $this->hat);
        $ret->headColor2 = $this->getSecondColor('head', $this->head);
        $ret->bodyColor2 = $this->getSecondColor('body', $this->body);
        $ret->feetColor2 = $this->getSecondColor('feet', $this->feet);
        $ret->exp_points = $this->exp_points;
        $ret->exp_to_rank = exp_required_for_ranking($this->rank + 1);

        return $ret;
    }

    private function safeExplode($str_arr)
    {
        return (isset($str_arr) && strlen($str_arr) > 0) ? explode(',', $str_arr) : array();
    }

    private function applyTempItems()
    {
        $temp_items = TemporaryItems::getItems($this->user_id, $this->guild_id);
        foreach ($temp_items as $item) {
            $this->setPart($item->type, $item->part_id, true);
        }
    }

    public function incExp($exp)
    {
        $max_rank = exp_required_for_ranking($this->rank + 1);
        $new_exp_total = $this->exp_points + $exp;
        $this->write("setExpGain`$this->exp_points`$new_exp_total`$max_rank");
        $this->exp_points += $exp;
        $this->exp_today += $exp;

        // rank up
        if ($this->exp_points >= $max_rank) {
            $this->rank++;
            $this->active_rank++;
            $this->exp_points = $this->exp_points - $max_rank;
            $this->write("setRank`$this->active_rank");
            if ($this->rank === 3 || $this->rank === 20) {
                $this->saveInfo();
            } else {
                $this->maybeSave();
            }
        }
    }

    public function maybeSave()
    {
        $time = time();
        if ($time - $this->last_save_time > 120) {
            $this->last_save_time = $time;
            $this->saveInfo();
        }
    }

    public function useRankToken()
    {
        if ($this->rt_used < $this->rt_available) {
            $this->rt_used++;
        }
        $this->active_rank = $this->rank + $this->rt_used;
    }

    public function unuseRankToken()
    {
        if ($this->rt_used > 0) {
            $this->rt_used--;
        }
        $this->active_rank = $this->rank + $this->rt_used;

        if ($this->speed + $this->acceleration + $this->jumping > 150 + $this->active_rank) {
            if ($this->speed > 0) {
                $this->speed--;
            } elseif ($this->acceleration > 0) {
                $this->acceleration--; // this will rarely trigger
            } elseif ($this->jumping > 0) {
                $this->jumping--; // this should never trigger
            }
        }
        $this->verifyStats();
    }


    // activates a rank token rental
    public function activateRankToken($quantity = 1)
    {
        $this->rt_available += $quantity;
        $this->rt_available = $this->rt_available > 21 ? 21 : $this->rt_available;
        $this->sendCustomizeInfo();
    }


    public function getChatCount()
    {
        $seconds = time() - $this->chat_time;
        $this->chat_count -= $seconds / 2;
        $this->chat_count = $this->chat_count >= 0 ? $this->chat_count : 0;
        return $this->chat_count;
    }


    public function isIgnoredId($id)
    {
        return !(array_search($id, $this->ignored_array) === false);
    }


    public function sendCustomizeInfo()
    {
        $this->socket->write(
            'setCustomizeInfo'
            .'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
            .'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
            .'`'.join(',', $this->getFullParts('hat'))
            .'`'.join(',', $this->getFullParts('head'))
            .'`'.join(',', $this->getFullParts('body'))
            .'`'.join(',', $this->getFullParts('feet'))
            .'`'.$this->getRealStatStr()
            .'`'.$this->active_rank
            .'`'.$this->rt_used.'`'.$this->rt_available
            .'`'.$this->hat_color_2.'`'.$this->head_color_2.'`'.$this->body_color_2.'`'.$this->feet_color_2
            .'`'.join(',', $this->getFullParts('eHat'))
            .'`'.join(',', $this->getFullParts('eHead'))
            .'`'.join(',', $this->getFullParts('eBody'))
            .'`'.join(',', $this->getFullParts('eFeet'))
            .'`'.((int) HappyHour::isActive())
        );
    }


    public function getRemoteInfo()
    {
        return 'createRemoteCharacter'
        .'`'.$this->temp_id.'`'.$this->name
        .'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
        .'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
        .'`'.$this->getSecondColor('hat', $this->hat)
        .'`'.$this->getSecondColor('head', $this->head)
        .'`'.$this->getSecondColor('body', $this->body)
        .'`'.$this->getSecondColor('feet', $this->feet);
    }


    public function getLocalInfo()
    {
        return 'createLocalCharacter'
        .'`'.$this->temp_id
        .'`'.$this->getStatStr()
        .'`'.$this->hat_color.'`'.$this->head_color.'`'.$this->body_color.'`'.$this->feet_color
        .'`'.$this->hat.'`'.$this->head.'`'.$this->body.'`'.$this->feet
        .'`'.$this->getSecondColor('hat', $this->hat)
        .'`'.$this->getSecondColor('head', $this->head)
        .'`'.$this->getSecondColor('body', $this->body)
        .'`'.$this->getSecondColor('feet', $this->feet);
    }


    public function getSecondColor($type, $id)
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

        return (array_search($id, $epic_arr) === false && array_search('*', $epic_arr) === false) ? -1 : $color;
    }


    public function awardKongParts()
    {
        $hat_added = $this->gainPart('hat', 3, true) || $this->gainPart('eHat', 3, true);
        $head_added = $this->gainPart('head', 20, true) || $this->gainPart('eHead', 20, true);
        $body_added = $this->gainPart('body', 17, true) || $this->gainPart('eBody', 17, true);
        $feet_added = $this->gainPart('feet', 16, true) || $this->gainPart('eFeet', 16, true);
        $this->hat_color = $hat_added ? 0x990000 : $this->hat_color;
        $this->head_color = $head_added ? 0x990000 : $this->head_color;
        $this->body_color = $body_added ? 0x990000 : $this->body_color;
        $this->feet_color = $feet_added ? 0x990000 : $this->feet_color;
        if ($hat_added || $head_added || $body_added || $feet_added) {
            $this->write("message`Thanks for honoring Kongregate's contributions to PR2's success! "
                ."The Kong Hat and the Ant Set have been added to your account.\n\nxoxo -Kong & Jiggmin");
        }
    }


    // call with & to write directly to the array
    private function determinePartArray($type)
    {
        $type = strtolower($type);
        if ($type === 'hat') {
            return 'hat_array';
        } elseif ($type === 'head') {
            return 'head_array';
        } elseif ($type === 'body') {
            return 'body_array';
        } elseif ($type === 'feet') {
            return 'feet_array';
        } elseif ($type === 'ehat') {
            return 'epic_hat_array';
        } elseif ($type === 'ehead') {
            return 'epic_head_array';
        } elseif ($type === 'ebody') {
            return 'epic_body_array';
        } elseif ($type === 'efeet') {
            return 'epic_feet_array';
        } else {
            output("Player->determinePartArray - unknown part type: $type");
            return false;
        }
    }


    public function hasPart($type, $id)
    {
        $arr_name = $this->determinePartArray($type);
        if ($arr_name !== false) {
            $arr = &$this->{$arr_name};
            if ($arr !== false && array_search($id, $arr) !== false) {
                return true;
            }
        }
        return false;
    }


    public function gainPart($type, $id, $autoset = false)
    {
        $arr_name = $this->determinePartArray($type);
        if ($arr_name !== false) {
            $arr = &$this->{$arr_name};
            if ($this->hasPart($type, $id) === false) {
                array_push($arr, $id);
                if ($autoset) {
                    $this->setPart($type, $id);
                    $this->write("wearingHat`$this->hat");
                }
                return true;
            }
        }
        return false;
    }


    public function setPart($type, $id)
    {
        if (strpos($type, 'e') === 0) {
            $type = substr($type, 1);
            $type = strtolower($type);
            if ($this->hasPart($type, $id) === false) {
                return; // only set if they have the base part
            }
        }

        if ($type === 'hat' || $type === 'head' || $type === 'body' || $type === 'feet') {
            $this->{$type} = $id;
        }
    }


    private function getStatStr()
    {
        if (HappyHour::isActive()) {
            $speed = $this->hh_speed;
            $accel = $this->hh_acceleration;
            $jump = $this->hh_jumping;
        } elseif (PR2SocketServer::$tournament) {
            $speed = PR2SocketServer::$tournament_speed;
            $accel = PR2SocketServer::$tournament_acceleration;
            $jump = PR2SocketServer::$tournament_jumping;
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
        return "$speed`$accel`$jump";
    }


    private function getRealStatStr()
    {
        $hh_active = HappyHour::isActive();
        $speed = $hh_active ? $this->hh_speed : $this->speed;
        $accel = $hh_active ? $this->hh_acceleration : $this->acceleration;
        $jump = $hh_active ? $this->hh_jumping : $this->jumping;
        return "$speed`$accel`$jump";
    }


    public function setCustomizeInfo($data)
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

        if (!HappyHour::isActive() && $speed + $acceleration + $jumping <= 150 + $this->active_rank) {
            $this->speed = $speed;
            $this->acceleration = $acceleration;
            $this->jumping = $jumping;
        } elseif (HappyHour::isActive() && $speed + $acceleration + $jumping <= 300) {
            $this->hh_speed = $speed;
            $this->hh_acceleration = $acceleration;
            $this->hh_jumping = $jumping;
        }

        $this->verifyParts();
        $this->verifyStats();
        $this->maybeSave();
    }


    private function verifyStats()
    {
        // determine which set of stats to check
        $hh_active = HappyHour::isActive();
        $speed = $hh_active ? $this->hh_speed : $this->speed;
        $accel = $hh_active ? $this->hh_acceleration : $this->acceleration;
        $jump = $hh_active ? $this->hh_jumping : $this->jumping;

        // sanity check: less than 0 or greater than 100?
        $speed = $speed < 0 ? 0 : ($speed > 100 ? 100 : $speed);
        $accel = $accel < 0 ? 0 : ($accel > 100 ? 100 : $accel);
        $jump = $jump < 0 ? 0 : ($jump > 100 ? 100 : $jump);

        // sanity check: total stat points out of bounds?
        if (!$hh_active && $speed + $accel + $jump > 150 + $this->active_rank) {
            $this->speed = 50;
            $this->acceleration = 50;
            $this->jumping = 50;
        } elseif ($hh_active && $speed + $accel + $jump > 300) {
            $this->hh_speed = 100;
            $this->hh_acceleration = 100;
            $this->hh_jumping = 100;
        }

        // apply to active stats
        $hh_pre = $hh_active ? 'hh_' : '';
        $this->{$hh_pre . 'speed'} = $speed;
        $this->{$hh_pre . 'acceleration'} = $accel;
        $this->{$hh_pre . 'jumping'} = $jump;
    }


    private function verifyParts($strict = false)
    {
        $this->verifyPart($strict, 'hat');
        $this->verifyPart($strict, 'head');
        $this->verifyPart($strict, 'body');
        $this->verifyPart($strict, 'feet');
    }


    private function verifyPart($strict, $type)
    {
        if (!isset($this->user_id)) {
            return false;
        }

        $parts_available = $strict ? $this->getOwnedParts($type) : $this->getFullParts($type);
        if (array_search($this->{$type}, $parts_available) === false) {
            $this->{$type} = $parts_available[0];
        }
    }


    private function getOwnedParts($type)
    {
        $is_e = substr($type, 0, 1) === 'e';
        return $is_e === true ? $this->{'epic_'.strtolower(substr($type, 1)).'_array'} : $this->{$type.'_array'};
    }


    private function getFullParts($type)
    {
        $perm = $this->getOwnedParts($type);
        $temp = TemporaryItems::getParts($type, $this->user_id, $this->guild_id);
        return array_merge($perm, $temp);
    }


    public function write($str)
    {
        if (isset($this->socket)) {
            $this->socket->write($str);
        }
    }


    public function wearingHat($hat_num)
    {
        $wearing = false;
        foreach ($this->worn_hat_array as $hat) {
            if ((int) $hat->num === $hat_num) {
                $wearing = true;
            }
        }
        return $wearing;
    }


    public function becomeTempMod()
    {
        $this->group = 2;
        $this->temp_mod = true;
        $this->write('becomeTempMod`');
    }


    public function becomeServerOwner()
    {
        $this->write('becomeTempMod`'); // to pop up the temp mod menu for server owners
        $this->server_owner = true;
        $this->group = 3;
        $this->write('setGroup`3');
        if ($this->user_id != FRED) {
            $this->write("message`Welcome to your private server! ".
                "You have admin privileges here. To promote server mods, ".
                "type /mod promote *player name here* in the chat. ".
                "They'll remain modded until they log out.<br><br>".
                "For more information about what commands you can use, ".
                "type /help in the chat.");
        }
    }


    public function becomeGuest()
    {
        $this->guest = true;
        $this->write("message`Welcome to Platform Racing 2!<br><br>".
            "You're a guest, which means you'll have limited privileges. ".
            "To gain full functionality, log out and create your own account. ".
            "<br><br>Thanks for playing, I hope you enjoy!<br>-Jiggmin");
    }


    public function saveInfo()
    {
        global $server_id;
        
        // make sure there's something to save
        if (!isset($this->user_id)) {
            return false;
        }

        // ensure no part arrays contain empty values
        foreach (range(0, (count($this->hat_array)-1)) as $num) {
            if (empty($this->hat_array[$num])) {
                unset($this->hat_array[$num]);
            }
        }
        foreach (range(0, (count($this->head_array)-1)) as $num) {
            if (empty($this->head_array[$num])) {
                unset($this->head_array[$num]);
            }
        }
        foreach (range(0, (count($this->body_array)-1)) as $num) {
            if (empty($this->body_array[$num])) {
                unset($this->body_array[$num]);
            }
        }
        foreach (range(0, (count($this->feet_array)-1)) as $num) {
            if (empty($this->feet_array[$num])) {
                unset($this->feet_array[$num]);
            }
        }

        // make sure none of the part values are blank to avoid server crashes, remove temp parts
        if (empty($this->hat) || !in_array($this->hat, $this->hat_array)) {
            $this->gainPart('hat', 1, true);
            $this->setPart('hat', 1);
        }
        if (empty($this->head) || !in_array($this->head, $this->head_array)) {
            $this->gainPart('head', 1, true);
            $this->setPart('head', 1);
        }
        if (empty($this->body) || !in_array($this->body, $this->body_array)) {
            $this->gainPart('body', 1, true);
            $this->setPart('body', 1);
        }
        if (empty($this->feet) || !in_array($this->feet, $this->feet_array)) {
            $this->gainPart('feet', 1, true);
            $this->setPart('feet', 1);
        }

        $rank = $this->rank;
        $exp_points = $this->exp_points;

        $hatCol = $this->hat_color;
        $headCol = $this->head_color;
        $bodyCol = $this->body_color;
        $feetCol = $this->feet_color;

        $hatCol2 = $this->hat_color_2;
        $headCol2 = $this->head_color_2;
        $bodyCol2 = $this->body_color_2;
        $feetCol2 = $this->feet_color_2;

        $hat = $this->hat;
        $head = $this->head;
        $body = $this->body;
        $feet = $this->feet;

        $hat_arr = join(',', $this->hat_array);
        $head_arr = join(',', $this->head_array);
        $body_arr = join(',', $this->body_array);
        $feet_arr = join(',', $this->feet_array);

        $ehat_arr = join(',', $this->epic_hat_array);
        $ehead_arr = join(',', $this->epic_head_array);
        $ebody_arr = join(',', $this->epic_body_array);
        $efeet_arr = join(',', $this->epic_feet_array);

        $speed = $this->speed;
        $accel = $this->acceleration;
        $jump = $this->jumping;

        $status = $this->status;
        $e_server_id = $status === 'offline' ? 0 : $server_id;

        $rt_used = $this->rt_used;
        $ip = $this->ip;
        $exp_gain = $this->exp_today - $this->start_exp_today;

        if ($this->group === 0 || $this->guest === true) {
            $rank = $exp_points = $rt_used = 0;
            $hat_arr = '1';
            $head_arr = $body_arr = $feet_arr = '1,2,3,4,5,6,7,8,9';
            $ehat_arr = $ehead_arr = $ebody_arr = $efeet_arr = '';
            $hat = $head = $body = $feet = 1;
            $speed = $accel = $jump = 50;
        }

        db_op('pr2_update', array(
            $this->user_id,
            $rank,
            $exp_points,
            $hatCol,
            $headCol,
            $bodyCol,
            $feetCol,
            $hatCol2,
            $headCol2,
            $bodyCol2,
            $feetCol2,
            $hat,
            $head,
            $body,
            $feet,
            $hat_arr,
            $head_arr,
            $body_arr,
            $feet_arr,
            $speed,
            $accel,
            $jump
        ));

        db_op('epic_upgrades_upsert', array($this->user_id, $ehat_arr, $ehead_arr, $ebody_arr, $efeet_arr));
        db_op('user_update_status', array($this->user_id, $status, $e_server_id));
        db_op('rank_token_update', array($this->user_id, $rt_used));
        db_op('exp_today_add', array('id-' . $this->user_id, $exp_gain));
        db_op('exp_today_add', array('ip-' . $ip, $exp_gain));
    }


    public function remove()
    {
        global $player_array;

        // get out of whatever you're in
        if (isset($this->right_room)) {
            $this->right_room->removePlayer($this);
        }
        if (isset($this->chat_room)) {
            $this->chat_room->removePlayer($this);
        }
        if (isset($this->game_room)) {
            $this->game_room->quitRace($this);
            $this->game_room->removePlayer($this);
        }
        if (isset($this->course_box)) {
            $this->course_box->clearSlot($this);
        }

        // make sure the socket is nice and dead
        if (is_object($this->socket)) {
            $this->socket->player = null;
            $this->socket->close();
            $this->socket->onDisconnect();
            $this->socket = null;
        }

        // remove from player array
        unset($player_array[$this->user_id]);

        // save info
        $this->status = 'offline';
        $this->verifyStats();
        $this->verifyParts(true);
        $this->saveInfo();

        // delete
        foreach ($this as $key => $var) {
            $this->$key = null;
            unset($this->$key, $key, $var);
        }
    }
}
