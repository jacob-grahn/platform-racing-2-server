<?php

namespace pr2\multi;

class Player
{
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

    public $worn_hat_array = array();

    public $domain;
    public $ip;

    public $temp_mod = false;
    public $trial_mod = false;
    public $server_owner = false;

    public $hh_warned = false;
    public $restart_warned = false;

    public $status = '';
    public $register_time;

    public $lives = 3;
    public $items_used = 0;
    public $super_booster = false;
    public $last_save_time = 0;


    public function __construct($socket, $login)
    {
        $this->socket = $socket;
        $this->ip = $socket->ip;
        $this->last_save_time = time();

        $this->user_id = (int) $login->user->user_id;
        $this->name = $login->user->name;
        $this->group = (int) $login->user->power;
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

        $this->speed = (int) $login->stats->speed;
        $this->acceleration = (int) $login->stats->acceleration;
        $this->jumping = (int) $login->stats->jumping;

        $this->friends_array = $login->friends;
        $this->ignored_array = $login->ignored;

        $this->domain = $login->login->domain;
        $this->version = $login->login->version;

        $this->rt_used = (int) $login->rt_used;
        $this->rt_available = (int) $login->rt_available;
        $this->exp_today = (int) $this->start_exp_today = (int) $login->exp_today;
        $this->last_exp_time = time();
        $this->status = $login->status;
        $this->register_time = (int) $login->user->register_time;

        $socket->player = $this;
        $this->active_rank = $this->rank + $this->rt_used;

        global $player_array;
        global $max_players;

        // check if the server is full
        $pCount = count($player_array);
        if (($pCount > $max_players && $this->group < 2) || ($pCount > ($max_players - 10) && $this->group === 0)) {
            $this->write('loginFailure`');
            $this->write('message`Sorry, this server is full. Try back later.');
            $this->remove();
        } // check for a valid rank
        elseif ($this->active_rank > 100 && $this->user_id != FRED) {
            $this->write('loginFailure`');
            $this->write('message`Your rank is too high. Please choose a different account.');
            $this->remove();
        } // add to the player array
        else {
            $player_array[$this->user_id] = $this;
        }

        // if they're a trial, tell the client
        if ($this->group === 2 && $login->user->trial_mod) {
            $this->trial_mod = true;
            $this->write("becomeTrialMod`");
        }

        if (isset($player_array[$this->user_id])) {
            $this->awardKongHat();
            $this->applyTempItems();
            $this->verifyStats();
            $this->verifyParts();
        }
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
        $max_rank = RankupCalculator::getExpRequired($this->active_rank + 1);
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
            $this->speed--;
        }
        $this->verifyStats();
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
        return array_search($id, $this->ignored_array) === false ? false : true;
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


    public function awardKongHat()
    {
        if (strpos($this->domain, 'kongregate.com') !== false) {
            $added = $this->gainPart('hat', 3, true);
            $this->hat_color = $added === true ? 10027008 : $this->hat_color;
            if ($this->guest === false && $added === true) {
                $this->write("message`Thanks for playing PR2 on Kongregate! "
                    ."As a token of our thanks, the Kong Hat has been added to your account.\n\nxoxo -Kong & Jiggmin");
            }
        }
    }


    public function awardKongOutfit()
    {
        $this->gainPart('head', 20, true);
        $this->gainPart('body', 17, true);
        $this->gainPart('feet', 16, true);
    }


    private function determinePartArray($type)
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
            output("Player->determinePartArray - unknown part type: $type");
            return false;
        }
        return $arr;
    }


    public function hasPart($type, $id)
    {
        $arr = $this->determinePartArray($type);
        if ($arr !== false && array_search($id, $arr) !== false) {
            return true;
        }
        return false;
    }


    public function gainPart($type, $id, $autoset = false)
    {
        $arr = $this->determinePartArray($type);
        if ($this->hasPart($type, $id) === false) {
            array_push($arr, $id);
            if ($autoset) {
                $this->setPart($type, $id);
            }
            return true;
        } else {
            return false;
        }
    }


    public function setPart($type, $id)
    {
        if (strpos($type, 'e') === 0) {
            $type = substr($type, 1);
            $type = strtolower($type);
        }

        if ($type === 'hat' || $type === 'head' || $type === 'body' || $type === 'feet') {
            $this->{$type} = $id;
        }
    }


    private function getStatStr()
    {
        if (HappyHour::isActive()) {
            $speed = 100;
            $accel = 100;
            $jump = 100;
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
        return "$this->speed`$this->acceleration`$this->jumping";
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

        if ($speed + $acceleration + $jumping <= 150 + $this->active_rank) {
            $this->speed = $speed;
            $this->acceleration = $acceleration;
            $this->jumping = $jumping;
        }

        $this->verifyParts();
        $this->verifyStats();
        $this->maybeSave();
    }


    private function verifyStats()
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
        
        $eType = 'e'.ucfirst($type);
        $part = $this->{$type};

        if ($strict) {
            $parts_available = $this->getOwnedParts($type);
            $epic_parts_available = $this->getOwnedParts($eType);
        } else {
            $parts_available = $this->getFullParts($type);
            $epic_parts_available = $this->getFullParts($eType);
        }
        
        if (array_search($part, $parts_available) === false) {
            $part = $parts_available[0];
            $this->{$type} = $part;
        }
    }


    private function getOwnedParts($type)
    {
        $is_e = substr($type, 0, 1) === 'e' ? true : false;
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
            if ($hat->num === $hat_num) {
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
        global $server_id, $pdo;
        
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

        // make sure none of the part values are blank to avoid server crashes
        if (empty($this->hat)) {
            $this->gainPart('hat', 1, true);
            $this->setPart('hat', 1);
        }
        if (empty($this->head)) {
            $this->gainPart('head', 1, true);
            $this->setPart('head', 1);
        }
        if (empty($this->body)) {
            $this->gainPart('body', 1, true);
            $this->setPart('body', 1);
        }
        if (empty($this->feet)) {
            $this->gainPart('feet', 1, true);
            $this->setPart('feet', 1);
        }

        // auto removing some hat?
        $index = array_search(27, $this->hat_array);
        if ($index !== false) {
            array_splice($this->hat_array, $index, 1);
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

        pr2_update(
            $pdo,
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
        );

        epic_upgrades_upsert($pdo, $this->user_id, $ehat_arr, $ehead_arr, $ebody_arr, $efeet_arr);
        user_update_status($pdo, $this->user_id, $status, $e_server_id);
        rank_token_update($pdo, $this->user_id, $rt_used);
        exp_today_add($pdo, 'id-' . $this->user_id, $exp_gain);
        exp_today_add($pdo, 'ip-' . $ip, $exp_gain);
    }


    public function remove()
    {
        global $player_array;

        unset($player_array[$this->user_id]);

        // make sure the socket is nice and dead
        if (is_object($this->socket)) {
            $this->socket->player = null;
            $this->socket->close();
            $this->socket->onDisconnect();
            $this->socket = null;
        }

        // get out of whatever you're in
        if (isset($this->right_room)) {
            $this->right_room->removePlayer($this);
        }
        if (isset($this->chat_room)) {
            $this->chat_room->removePlayer($this);
        }
        if (isset($this->game_room)) {
            $this->game_room->removePlayer($this);
        }
        if (isset($this->course_box)) {
            $this->course_box->clearSlot($this);
        }

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
