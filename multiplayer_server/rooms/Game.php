<?php

namespace pr2\multi;

class Game extends Room
{

    const LEVEL_COMPASS = 3236908; // for top hat
    const LEVEL_BUTO = 1738847; // for jigg hat
    const LEVEL_DELIVERANCE = 1896157; // for slender set
    const LEVEL_SEA = 2255404; // for sea set
    const LEVEL_DEEPER = 6493337; // for jellyfish hat
    const LEVEL_HAUNTED = 1782114; // for epic jack-o'-lantern head
    const LEVEL_CHEESE = 6207945; // for cheese hat

    const MODE_RACE = 'race';
    const MODE_DEATHMATCH = 'deathmatch';
    const MODE_EGG = 'egg';
    const MODE_OBJECTIVE = 'objective';
    const MODE_HAT = 'hat';

    const PLAYER_SIR = 5321458; // sir sirlington
    const PLAYER_CLINT = 5451130; // clint the cowboy
    
    private $finish_array = array();
    private $course_id;
    private $from_room;
    private $start_time;
    private $begun = false;
    private $loose_hat_array = array();
    private $next_hat_id = 0;
    private $prize;
    private $prize_cancelled = false;
    private $campaign;

    private $mode = self::MODE_RACE;
    private $hatCountdownEnd = -1;
    private $hasHats = -1;
    private $ending_egg = false;
    private $finish_count = 0;
    private $finish_positions = array();
    private $cowboy_chance = '';
    private $cowboy_mode = false;
    private $tournament = false;

    protected $room_name = 'game_room';
    protected $temp_id = 0;


    public function __construct($course_id, $from_room)
    {
        $this->course_id = $course_id;
        $this->from_room = $from_room;
        $this->tournament = PR2SocketServer::$tournament;
        $this->start_time = microtime(true);
    }


    public function addPlayer($player)
    {
        if (count($this->finish_array) < 4) {
            Room::addPlayer($player);
            $player->socket->write('tournamentMode`' . (int) PR2SocketServer::$tournament);
            $player->socket->write('startGame`'.$this->course_id);
            $player->temp_id = $this->temp_id;
            $player->pos_x = 0;
            $player->pos_y = 0;
            $player->average_vel_x = 0;
            $player->average_vel_y = 0;
            $player->lives = 3;
            $this->temp_id++;
            $race_stats = new RaceStats($player);
            array_push($this->finish_array, $race_stats);
            $player->race_stats = $race_stats;
        }
    }


    public function removePlayer($player)
    {
        Room::removePlayer($player);

        $this->finishDrawing($player);
        $player->race_stats->still_here = false;

        // ATTN: rework to do quitRace instead? so the hat attack mode detection stuff actually works??
        if (!isset($player->race_stats->finish_time)) {
            $this->setFinishTime($player, 'forfeit');
        } else {
            $this->broadcastFinishTimes();
        }

        $player->race_stats = null;
        $player->temp_id = null;
        unset($player->temp_id);

        if (count($this->player_array) <= 0) {
            $this->remove();
        }
    }


    public function init()
    {
        $this->recordPlays();
        $this->determinePrize();

        //send character info
        foreach ($this->player_array as $player) {
            $player->race_stats->finished_race = false;
            $player->socket->write($player->getLocalInfo());
            $this->sendToRoom($player->getRemoteInfo(), $player->user_id);
        }

        //super booster
        if (!$this->tournament) {
            foreach ($this->player_array as $player) {
                if ($player->super_booster) {
                    $player->super_booster = false;
                    $this->sendToRoom('superBooster`' . $player->temp_id, -1);
                }
            }
        }

        //happy hour
        if (HappyHour::isActive()) {
            $this->sendToAll('happyHour`');
        }

        //tournament
        if ($this->tournament) {
            announce_tournament($this);
        }
    }


    private function recordPlays()
    {
        global $play_count_array;
        $player_count = count($this->player_array);
        if (isset($play_count_array[$this->course_id])) {
            $play_count_array[$this->course_id] += $player_count;
        } else {
            $play_count_array[$this->course_id] = $player_count;
        }
    }


    // check if a player is present
    private function isPlayerHere($player_id)
    {
        $ret = false;
        foreach ($this->player_array as $player) {
            if ($player->user_id == $player_id) {
                $ret = true;
            }
        }
        return $ret;
    }


    private function determinePrize()
    {
        $player_count = count($this->player_array);

        global $campaign_array;
        if (isset($campaign_array[$this->course_id])) {
            $this->campaign = $campaign_array[$this->course_id];
        }

        // campaign prizes
        if (isset($this->campaign)) {
            $campaign_prize = Prizes::find($this->campaign->prize_type, $this->campaign->prize_id);
            if ($player_count >= 4 || (isset($campaign_prize) && $campaign_prize->isUniversal())) {
                $this->prize = $campaign_prize;
            }
        }

        // Haunted House 2 by DareDevil1510; Awards: Epic Jack-o'-Lantern Head
        if ($this->course_id == self::LEVEL_HAUNTED) {
            $this->prize = Prizes::$EPIC_JACKOLANTERN_HEAD;
        }

        // -Deliverance- by changelings; Awards: Slender Set
        if ($this->course_id == self::LEVEL_DELIVERANCE) {
            $slender_prizes = array(Prizes::$SLENDER_HEAD, Prizes::$SLENDER_BODY, Prizes::$SLENDER_FEET);
            $this->prize = $slender_prizes[array_rand($slender_prizes)];
        }

        // ~Under the sea~ by Rammjet; Awards: Sea Set
        if ($this->course_id == self::LEVEL_SEA) {
            $sea_prizes = array(Prizes::$SEA_HEAD, Prizes::$SEA_BODY, Prizes::$SEA_FEET);
            $this->prize = $sea_prizes[array_rand($sea_prizes)];
        }

        // The Golden Compass by -Shadowfax-; Awards: Top Hat
        if ($this->course_id == self::LEVEL_COMPASS) {
            $this->prize = Prizes::$TOP_HAT;
        }

        // Deeper by Sothal; Awards: Jellyfish Hat
        if ($this->course_id == self::LEVEL_DEEPER) {
            $this->prize = Prizes::$JELLYFISH_HAT;
        }
        
        
        // Sir Sirlington; Awards: Epic Sir Set + Epic Top Hat
        if ($this->isPlayerHere(self::PLAYER_SIR)) {
            $sir_prizes = [
                Prizes::$EPIC_TOP_HAT,
                Prizes::$EPIC_SIR_HEAD,
                Prizes::$EPIC_SIR_BODY,
                Prizes::$EPIC_SIR_FEET
            ];
            $this->prize = $sir_prizes[array_rand($sir_prizes)];
        }

        // Clint the Cowboy; Awards: Epic Cowboy Hat
        if ($this->isPlayerHere(self::PLAYER_CLINT)) {
            $this->prize = Prizes::$EPIC_COWBOY_HAT;
        }

        // random part/upgrade prizes
        if (!isset($this->prize) && $player_count >= 1) {
            if (rand($player_count*2, 20) >= 19) {
                $prize_array = array(
                Prizes::$TACO_HEAD,
                Prizes::$TACO_BODY,
                Prizes::$TACO_FEET,
                Prizes::$INVISIBLE_HEAD,
                Prizes::$INVISIBLE_BODY,
                Prizes::$INVISIBLE_FEET,
                Prizes::$GINGERBREAD_HEAD,
                Prizes::$GINGERBREAD_BODY,
                Prizes::$GINGERBREAD_FEET,
                Prizes::$STICK_HEAD,
                Prizes::$STICK_BODY,
                Prizes::$STICK_FEET,
                Prizes::$SIR_HEAD,
                Prizes::$SIR_BODY,
                Prizes::$SIR_FEET,
                Prizes::$BASKETBALL_HEAD,
                Prizes::$ARMOR_HEAD,
                Prizes::$EPIC_CLASSIC_HEAD,
                Prizes::$EPIC_CLASSIC_BODY,
                Prizes::$EPIC_CLASSIC_FEET,
                Prizes::$EPIC_TIRED_HEAD,
                Prizes::$EPIC_DRESS_BODY,
                Prizes::$EPIC_SANDAL_FEET,
                Prizes::$EPIC_FLOWER_HEAD,
                Prizes::$EPIC_STRAP_BODY,
                Prizes::$EPIC_HEEL_FEET
                );
                $this->prize = $prize_array[rand(0, count($prize_array)-1)];
            }
        }

        // random hat prizes
        if (!isset($this->prize) && $player_count >= 2) {
            if (rand(0, 40) == 40) {
                $this->prize = Prizes::$EXP_HAT;
            }
            if (rand(0, 45) == 45) {
                $this->prize = Prizes::$SANTA_HAT;
            }
            if (rand(0, 50) == 50) {
                $this->prize = Prizes::$PARTY_HAT;
            }
            if (rand(0, 40) == 40 && HappyHour::isActive()) {
                $this->prize = Prizes::$JUMP_START_HAT;
            }
        }

        // don't set prizes on servers with tournament mode enabled
        if (PR2SocketServer::$no_prizes) {
            $this->prize = null;
        }

        // tell the world
        if (isset($this->prize)) {
            $this->sendToAll('setPrize`'.$this->prize->toStr());
        }
    }


    public function prizerSetPrize($user_id, $type, $id)
    {
        if (PR2SocketServer::$prizer_id !== 0 && $user_id === PR2SocketServer::$prizer_id) {
            $prize = Prizes::find($type, $id);
            if (isset($prize)) {
                $this->prize = $prize;
                $this->prize_cancelled = false;
                $this->sendToAll('setPrize`'.$this->prize->toStr());
            }
        }
    }


    public function cancelPrize($player)
    {
        if ($this->prize_cancelled === true || !isset($this->prize)) {
            return; // no point in continuing...
        }
        $clint_cond = $player->user_id === self::PLAYER_CLINT && $this->prize == Prizes::$EPIC_COWBOY_HAT;
        $sir_cond = $player->user_id === self::PLAYER_SIR && (
            $this->prize == Prizes::$EPIC_TOP_HAT ||
            $this->prize == Prizes::$EPIC_SIR_HEAD ||
            $this->prize == Prizes::$EPIC_SIR_BODY ||
            $this->prize == Prizes::$EPIC_SIR_FEET
        );
        $prizer_cond = PR2SocketServer::$prizer_id === $player->user_id;
        if ($sir_cond || $clint_cond || $player->group === 3 || $prizer_cond) {
            $this->prize = null;
            $this->prize_cancelled = true;
            $this->sendToAll("cancelPrize`$player->name");
        }
    }


    public function finishDrawing($player, $data = null)
    {
        if ($player->race_stats->drawing === true) {
            $arr = explode('`', $data);
            $player->race_stats->drawing = false;
            if (isset($data)) {
                $rs = $player->race_stats;
                $rs->level_hash = $arr[0];
                $rs->mode = $arr[1];
                $rs->finish_positions = $arr[2];
                $rs->finish_count = $arr[3];
                $rs->cowboy_chance = $arr[4];
            }
            $this->sendToAll('finishDrawing`'.$player->temp_id);
        }
        $this->maybeBeginRace();
    }


    private function maybeBeginRace()
    {
        $begin_race = true;
        foreach ($this->player_array as $player) {
            if ($player->race_stats->drawing === true) {
                $begin_race = false;
                break;
            }
        }

        if ($begin_race && !$this->begun) {
            $this->beginRace();
        }
    }


    private function beginRace()
    {
        if (!$this->begun) {
            $this->begun = true;
            $this->mode = $this->democratize('mode');
            $this->hash = $this->democratize('level_hash');
            $this->finish_positions = $this->democratize('finish_positions');
            $this->finish_count = $this->democratize('finish_count');
            $this->cowboy_chance = $this->democratize('cowboy_chance');

            // turn finish positions into an array
            if ($this->finish_positions != 'all') {
                $this->finish_positions = json_decode($this->finish_positions);
            }

            // don't start a hat attack level if there's only one player in the game
            if ($this->mode === self::MODE_HAT && count($this->player_array) <= 1) {
                foreach ($this->player_array as $player) {
                    $this->quitRace($player);
                    $player->socket->write("forceQuit`");
                    $hat_msg = 'Error: You can\'t play a hat attack level by yourself. :(';
                    $player->socket->write("message`$hat_msg");
                }
                return;
            }

            // boot people with the wrong level hash
            foreach ($this->player_array as $player) {
                if ($this->hash !== $player->race_stats->level_hash) {
                    $this->quitRace($player);
                }
            }

            // jigg hat
            if ($this->course_id == self::LEVEL_BUTO) {
                $hat = $this->makeHat($this->next_hat_id++, Hats::JIGG, 0xFFFFFF, -1);
                $this->loose_hat_array[$hat->id] = $hat;
                $x = 13450;
                $y = -6200;
                $rot = 0;
                $this->sendToAll("addEffect`Hat`$x`$y`$rot`$hat->num`$hat->color`$hat->color2`$hat->id", -1);
            }

            // cheese hat
            if ($this->course_id == self::LEVEL_CHEESE) {
                $hat = $this->makeHat($this->next_hat_id++, Hats::CHEESE, 0xFFD860, 0x000000);
                $this->loose_hat_array[$hat->id] = $hat;
                $x = 13878;
                $y = 7214;
                $rot = 0;
                $this->sendToAll("addEffect`Hat`$x`$y`$rot`$hat->num`$hat->color`$hat->color2`$hat->id", -1);
            }

            // place artifact hat
            if ($this->course_id == Artifact::$level_id) {
                $hat = $this->makeHat($this->next_hat_id++, Hats::ARTIFACT, 0xFFFFFF, -1);
                $this->loose_hat_array[$hat->id] = $hat;
                $x = Artifact::$x;
                $y = Artifact::$y;
                $rot = Artifact::$rot;
                $this->sendToAll("addEffect`Hat`$x`$y`$rot`$hat->num`$hat->color`$hat->color2`$hat->id", -1);
            }

            // eggs
            if ($this->mode == self::MODE_EGG) {
                $this->sendToAll('setEggSeed`'.rand(0, 99999));
                $this->sendToAll('addEggs`10');
            }

            // sfchm
            if ($this->cowboy_chance === '') {
                $this->cowboy_chance = 5;
            }
            if ($this->tournament && $this->cowboy_chance != 100) {
                $this->cowboy_chance = 0;
            }
            if (!isset($this->campaign) && rand(1, 100) <= $this->cowboy_chance) {
                $this->cowboy_mode = true;
                $this->sendToAll('cowboyMode`');
            }

            // hats
            $this->initHats();

            // start
            $this->start_time = microtime(true);
            $this->sendToAll('ping`' . time());
            $this->sendToAll('beginRace`');

            // if anyone forfeited, show it properly
            $this->broadcastFinishTimes();
        }
    }


    private function initHats()
    {
        foreach ($this->player_array as $player) {
            $player->worn_hat_array = array();
            $hat_id = $player->hat;
            $hat_color = $player->hat_color;
            $hat_color2 = $player->getSecondColor('hat', $hat_id);

            // change hat to tournament mode hat when enabled
            if ($this->tournament) {
                $hat_id = PR2SocketServer::$tournament_hat;
                if ($this->mode === self::MODE_HAT && $hat_id == 1) {
                    $hat_id = 2; // change nothing to exp hat when tournament mode is enabled on hat attack
                }
            }

            // remove artifact hat a player is wearing on artifact level
            if ($this->course_id == Artifact::$level_id && $hat_id == 14) {
                $hat_id = 1;
            }

            // cowboy mode
            if ($this->cowboy_mode) {
                $hat_id = 5;
            }

            // change the hat to something random during hat attack if they aren't wearing a valid hat
            // this is foolproof. no chance at all that this is a horrible idea. none whatsoever
            $valid_hats = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 15, 16];
            if ($this->mode === self::MODE_HAT && !in_array($hat_id, $valid_hats)) {
                $hat_id = $valid_hats[rand(0, count($valid_hats) - 1)];
                $msg = 'Howdy! Here\'s a random hat to use just for this level. Thank me later!!';
                $player->socket->write("chat`Fred the G. Cactus`3`$msg");
            }

            // init the hat and add to this player's worn hat array
            if ($hat_id != 1) {
                $hat = $this->makeHat($this->next_hat_id++, $hat_id, $hat_color, $hat_color2);
                $player->worn_hat_array[0] = $hat;
            }

            // send this player's hat array to all players
            $this->sendToAll($this->getHatStr($player));
        }
    }


    private function makeHat($id, $num, $color, $color2)
    {
        $hat = new \stdClass();
        $hat->id = $id;
        $hat->num = $num;
        $hat->color = $color;
        $hat->color2 = $color2;
        return $hat;
    }


    private function democratize($var)
    {
        $winner = '';
        $candidates = array();
        foreach ($this->player_array as $player) {
            $candidate = $player->race_stats->{$var};
            if ($candidate != '') {
                if (!isset($candidates[$candidate])) {
                    $candidates[$candidate] = 0;
                }
                $candidates[ $candidate ]++;
            }
        }
        arsort($candidates);
        reset($candidates);
        $winner = key($candidates);
        return $winner;
    }


    public function remoteFinishRace($player, $data)
    {
        if ($this->isStillPlaying($player->temp_id)) {
            if ($this->mode == self::MODE_RACE) {
                list($finish_id, $x, $y) = explode('`', $data);
                $this->verifyFinishPosition($x, $y, $finish_id);
            } elseif ($this->mode == self::MODE_HAT) {
                $msg = 'Psst... finish blocks don\'t do anything in hat attack mode!';
                $player->socket->write("chat`Fred the G. Cactus`3`$msg");
                return;
            }
            $this->finishRace($player);
        }
    }


    private function timeFormat($time, $digits = 2)
    {
        if ($time < 0) {
            $time = 0;
        }
        return round($time, $digits);
    }


    public function finishRace($player)
    {
        if ($player->race_stats->finished_race === false
            && !isset($player->race_stats->finish_time)
            && $player->race_stats->drawing === false
            && $this->begun === true
        ) {
            // get/format/validate/set finish time
            $finish_microtime = microtime(true);
            $full_time = $finish_microtime - $this->start_time;
            $finish_time = $this->timeFormat($full_time);
            $broadcast_time = $this->timeFormat($full_time, 3);
            $finish_time = $finish_time > 31536000 ? 0 : $finish_time; // if the race time > 1 year, set it to 0
            $this->setFinishTime($player, $finish_time);

            // exp time modifier (propotional before 2 mins)
            $time_mod = $finish_time / 120;
            $time_mod = $time_mod > 1 ? 1 : $time_mod;

            // checks for a "true" finish for awarding prizes
            $true_fin = strpos($player->race_stats->finish_time, 'forfeit') !== 0;

            // check if all objectives reached
            $completed_perc = 0;
            if ($this->mode == self::MODE_OBJECTIVE && $this->finish_count > 0) {
                $objective_count = count($player->race_stats->objectives_reached);
                $objective_count = $objective_count > $this->finish_count ? $this->finish_count : $objective_count;
                $completed_perc = $objective_count / $this->finish_count;
                $true_fin = $completed_perc == 1; // falsify true finish if all objs not reached
            }

            // get finish placement
            $place = array_search($player->race_stats, $this->finish_array);

            // announce on tournament server
            if ($true_fin && $place == 0 && count($this->finish_array) > 1 && $finish_time > 10 && $this->tournament) {
                $this->broadcastResults($player, $broadcast_time);
            }

            // init winning prize
            $prize = null;

            // set for winner or universal
            if (isset($this->prize) && ($place == 0 || $this->prize->isUniversal())) {
                $prize = $this->prize;
            }

            // set for buto
            $isButo = $this->course_id == self::LEVEL_BUTO;
            if ($isButo && $player->wearingHat(Hats::JIGG) && !$player->hasPart('hat', Hats::JIGG)) {
                $prize = Prizes::$JIGG_HAT;
            }

            // set for cheese
            $isCheese = $this->course_id == self::LEVEL_CHEESE;
            if ($isCheese && $player->wearingHat(Hats::CHEESE) && !$player->hasPart('hat', Hats::CHEESE)) {
                $prize = Prizes::$CHEESE_HAT;
            }

            // award prize to player
            if (isset($prize) && $true_fin) {
                $autoset = $prize->getType() == 'hat';
                $result = $player->gainPart($prize->getType(), $prize->getId(), $autoset);
                if ($result == true) {
                    $player->write('winPrize`' . $prize->toStr());
                }
            }

            // exp gain
            $tot_exp_gain = 0;

            // welcome back bonus
            $welcome_back_bonus = 0;
            if ($player->exp_today == 0 && $player->rank >= 5) {
                $welcome_back_bonus = 1000;
            } // level bonus
            else {
                $level_bonus = $this->applyExpCurve($player, 25 * $time_mod);
                
                // sanity check, think it works fine here
                $level_bonus = $level_bonus >= 5 && $finish_time <= 3 ? 0 : $level_bonus;

                // update level bonus proportionally according to objs reached in obj mode
                if ($this->mode == self::MODE_OBJECTIVE && $this->finish_count > 0) {
                    $level_bonus *= $completed_perc;
                }

                // round level bonus for int exp
                $level_bonus = round($level_bonus);

                // make level bonus 0 on tournament
                $level_bonus = PR2SocketServer::$no_prizes ? 0 : $level_bonus;

                // write award back to player and add to total exp gain
                if ($this->mode == self::MODE_DEATHMATCH) {
                    $player->write('award`Survival Bonus`+ '.$level_bonus);
                } elseif ($this->mode == self::MODE_OBJECTIVE && $completed_perc < 1) {
                    $player->write('award`Level Attempted`+ '.$level_bonus);
                } else {
                    $player->write('award`Level Completed`+ '.$level_bonus);
                }
                $tot_exp_gain += $level_bonus;
            }

            // opponent bonus
            for ($i = $place + 1; $i < count($this->finish_array); $i++) {
                $race_stats = $this->finish_array[$i];
                if ($race_stats->rank < 100 && PR2SocketServer::$no_prizes === false) {
                    $exp_gain = ($race_stats->rank+5) * $time_mod;
                    $exp_gain = ceil($this->applyExpCurve($player, $exp_gain));
                } else {
                    $exp_gain = 0;
                }
                $tot_exp_gain += $exp_gain;
                $player->write('award`Defeated '.$race_stats->name.'`+ '.$exp_gain);
            }

            // handle hats
            $hat_bonus = 0;
            $wearing_kong = false;
            foreach ($player->worn_hat_array as $hat) {
                if ($hat->num == 2) {
                    $hat_bonus += 1;
                } elseif ($hat->num == 3) {
                    $wearing_kong = true;
                }
            }

            // remove hats after finishing in hat attack mode
            if ($this->mode === self::MODE_HAT) {
                $this->loseAllHats($player);
            }

            // handle gp gain
            if ($place == 0 && count($this->finish_array) > 1 && $finish_time > 10) {
                $this->giveGp($player, $wearing_kong);
            }

            // apply exp bonuses to total exp multiplier and multiply total exp gain by anything less than 12
            $exp_multiplier = 1;
            $exp_multiplier += $hat_bonus > 4 ? 4 : $hat_bonus;
            $exp_multiplier *= HappyHour::isActive() ? 2 : 1;
            $exp_multiplier += isset($this->campaign) && $this->from_room === 'campaign' ? 2 : 0;
            $tot_exp_gain = round($tot_exp_gain * ($exp_multiplier > 12 ? 12 : $exp_multiplier));

            // exp award notification
            if ($exp_multiplier > 1) {
                $bonuses = new \stdClass();
                $bonuses->hat = $hat_bonus > 0;
                $bonuses->campaign = isset($this->campaign) && $this->from_room === 'campaign';
                $bonuses->hh = HappyHour::isActive();

                // determine number of awards
                $awards_arr = array();
                $num = 0;
                foreach ($bonuses as $key => $var) {
                    // not awarding?
                    if ($var !== true) {
                        continue;
                    }

                    // format, push to array, increment loop count
                    $key = $key === 'hh' ? ($bonuses->campaign ? strtoupper($key) : 'Happy Hour') : ucfirst($key);
                    array_push($awards_arr, $key);
                    $num++;
                }

                // build string
                $awards_str = $num > 2 || $bonuses->hh ? join('/', $awards_arr) : join(' & ', $awards_arr);
                $awards_str .= $num > 1 ? ' Bonuses' : ' Bonus';

                // return to player
                $player->write("award`$awards_str`exp X $exp_multiplier");
            }

            // apply welcome back bonus after all multipliers
            if ($welcome_back_bonus > 0) {
                $tot_exp_gain += $welcome_back_bonus;
                $player->write('award`Welcome Back Bonus`+ 1,000');
            }

            // apply artifact bonus after all multipliers
            if ($this->course_id == Artifact::$level_id && $player->wearingHat(Hats::ARTIFACT) && $true_fin) {
                $result = save_finder($player);
                if ($result) {
                    $max_artifact_bonus = 50000;
                    $artifact_bonus = round($max_artifact_bonus * $player->active_rank / 60);
                    if ($artifact_bonus > $max_artifact_bonus) {
                        $artifact_bonus = $max_artifact_bonus;
                    }
                    $user_id = (int) $player->user_id;
                    if (Artifact::$first_finder === $user_id && Artifact::$bubbles_winner !== $user_id) {
                        $artifact_bonus += 10000;
                    }
                    if ($artifact_bonus > 0) {
                        $tot_exp_gain += $artifact_bonus;
                        $player->write('award`Artifact Found!`+ ' . number_format($artifact_bonus));
                    }
                }
            }

            // reset unrealistic EXP gain
            $tot_exp_gain = $tot_exp_gain > 100000 ? 0 : $tot_exp_gain;

            // disconnect anyone trying to earn exp too quick
            if ($player->last_exp_time >= (time() - 2)) {
                $player->socket->write("message`Botting is a no-no. :(");
                $player->remove();
            }

            // log/increment exp and maybe save
            $player->last_exp_time = time();
            $player->incExp($tot_exp_gain);
            $player->maybeSave();
        } else {
            $this->setFinishTime($player, 'forfeit');
        }

        // they finished
        $player->race_stats->finished_race = true;

        // everyone finishes at the same time in egg mode
        $this->maybeEndEgg();
    }


    private function broadcastResults($player, $finish_time)
    {
        global $chat_room_array;
        $finish_int = (int) floor($finish_time);
        $minutes = (int) floor($finish_int / 60);
        $seconds = str_pad(floor($finish_time - ($minutes * 60)), 2, 0, STR_PAD_LEFT);
        $milliseconds = round(($finish_time - ($minutes * 60) - $seconds), 3);
        $milliseconds = str_pad(substr($milliseconds, 2), 3, 0);
        $str = $minutes . ':' . $seconds . '.' . $milliseconds;

        if (isset($chat_room_array['main'])) {
            $main = $chat_room_array['main'];
            $message = '';
            $names = array();
            foreach ($this->finish_array as $rs) {
                $names[] = userify($this->idToPlayer($rs->temp_id), $rs->name, $rs->group, $rs->mod_power);
            }
            $vs_names = join(' vs ', $names);
            $html_name = userify($player, $player->name, $player->group, $player->modPower());
            $message = "$vs_names: // $html_name wins with a time of $str!";
            $main->sendChat("systemChat`$message", -1);
        }
    }


    private function maybeEndDeathmatch()
    {
        if ($this->mode == self::MODE_DEATHMATCH) {
            $unfinished = 0;
            foreach ($this->finish_array as $race_stats) {
                if (!isset($race_stats->finish_time)) {
                    $unfinished++;
                }
            }
            if ($unfinished === 1) {
                foreach ($this->player_array as $player) {
                    if (!$player->race_stats->finished_race && !isset($player->auto_win_deathmatch)) {
                        $last_player = $player;
                    }
                }
                if (isset($last_player)) {
                    $last_player->auto_win_deathmatch = true;
                    $this->start_time -= 1;
                    $this->finishRace($last_player);
                    unset($last_player->auto_win_deathmatch);
                }
            }
        }
    }


    private function maybeEndEgg()
    {
        if ($this->mode === self::MODE_EGG && !$this->ending_egg) {
            $someone_finished = false;
            $everyone_quit = true;

            foreach ($this->player_array as $player) {
                if ($player->race_stats->finished_race) {
                    $someone_finished = true;
                    break;
                }
            }

            foreach ($this->player_array as $player) {
                if (!$player->race_stats->quit_race) {
                    $everyone_quit = false;
                    break;
                }
            }

            if ($someone_finished || $everyone_quit) {
                $this->ending_egg = true;
                foreach ($this->player_array as $player) {
                    if (!$player->race_stats->finished_race) {
                        $this->finishRace($player);
                    }
                }
            }
        }
    }


    public function quitRace($player)
    {
        $this->finishDrawing($player);
        if ($player->race_stats->finished_race == false) {
            $player->race_stats->quit_race = true;

            if ($this->mode === self::MODE_HAT) {
                $this->loseAllHats($player);
            }

            if ($this->mode == self::MODE_DEATHMATCH && $this->begun) {
                $this->finishRace($player);
            } elseif ($this->mode == self::MODE_OBJECTIVE && $this->begun) {
                $this->finishRace($player);
            } elseif ($this->mode === self::MODE_EGG) {
                $this->maybeEndEgg();
            } elseif ($this->mode === self::MODE_RACE || $this->mode === self::MODE_HAT) {
                $player->race_stats->finished_race = true;
                $this->setFinishTime($player, 'forfeit');
            }
        }
    }


    protected function sortFinishArrayRace($a, $b)
    {
        $a_time = $a->finish_time;
        $b_time = $b->finish_time;

        if (!isset($a_time)) {
            $a_time = 9999998;
        }
        if (!isset($b_time)) {
            $b_time = 9999998;
        }
        if ($a_time == 'forfeit') {
            $a_time = 9999999;
        }
        if ($b_time == 'forfeit') {
            $b_time = 9999999;
        }

        if ($a_time == $b_time) {
            return 0;
        } elseif ($a_time < $b_time) {
            return -1;
        } else {
            return 1;
        }
    }


    protected function sortFinishArrayDeathmatch($a, $b)
    {
        $a_time = $a->finish_time;
        $b_time = $b->finish_time;

        if (!isset($a_time)) {
            $a_time = 9999998;
        }
        if (!isset($b_time)) {
            $b_time = 9999998;
        }
        if ($a_time === 'forfeit') {
            $a_time = 0;
        }
        if ($b_time === 'forfeit') {
            $b_time = 0;
        }

        if ($a_time === $b_time) {
            return 0;
        } elseif ($a_time < $b_time) {
            return 1;
        } else {
            return -1;
        }
    }


    // more objectives makes the winner. objective ties are determined by finish times
    protected function sortFinishArrayObjective($a, $b)
    {
        $ao = count($a->objectives_reached);
        $bo = count($b->objectives_reached);
        $at = $a->last_objective_time;
        $bt = $b->last_objective_time;

        if ($ao < $bo) {
            return 1;
        } elseif ($ao > $bo) {
            return -1;
        } else {
            if ($at < $bt) {
                return -1;
            } elseif ($at > $bt) {
                return 1;
            } else {
                return 0;
            }
        }
    }


    protected function sortFinishArrayEgg($a, $b)
    {
        if ($a->eggs < $b->eggs) {
            return 1;
        } elseif ($a->eggs > $b->eggs) {
            return -1;
        } else {
            return 0;
        }
    }


    protected function sortFinishArrayHat($a, $b)
    {
        return $this->sortFinishArrayRace($a, $b);
    }


    private function setFinishTime($player, $finish_time)
    {
        if (!isset($player->race_stats->finish_time)) {
            $player->race_stats->finish_time = $finish_time;
        }
        $function_name = 'sortFinishArray' . ucfirst($this->mode);
        @usort($this->finish_array, array($this, $function_name));

        $this->broadcastFinishTimes();

        $this->sendToAll('var'.$player->temp_id.'`beginRemove`1');
        $this->maybeEndDeathmatch();
    }


    private function broadcastFinishTimes()
    {
        $str = 'finishTimes';

        // still drawing?
        $drawing = false;
        foreach ($this->player_array as $player) {
            $rs = $player->race_stats;
            if ($rs->drawing === true) {
                $drawing = true;
                break;
            }
        }

        // if still drawing, preserve the drawing animation
        if ($drawing === true) {
            foreach ($this->finish_array as $rs) {
                $player = $this->idToPlayer($rs->temp_id);
                $forfeit = $rs->quit_race ? ($this->mode === self::MODE_EGG ? '0' : 'forfeit') : '';
                $str .= '`' . $rs->name . '`' . $forfeit . '`' . $rs->drawing . '`' . $rs->still_here;
            }
        } // if not, broadcast as normal
        else {
            foreach ($this->finish_array as $rs) {
                if ($this->mode === self::MODE_EGG) {
                    $finish_time = $rs->eggs;
                } elseif ($this->mode === self::MODE_OBJECTIVE) {
                    if (!empty($rs->finish_time)) {
                        $obj_reached = count($rs->objectives_reached);
                        $finish_time = "$rs->finish_time,$obj_reached,$this->finish_count";
                    }
                } else {
                    $finish_time = $rs->finish_time;
                }
                if (isset($finish_time) || $rs->quit_race) {
                    $finish_time = isset($finish_time) ? $finish_time : ($rs->quit_race ? 'forfeit' : $finish_time);
                    $str .= '`' . $rs->name . '`' . $finish_time . '`' . $rs->drawing . '`' . $rs->still_here;
                }
            }
        }
        $this->sendToAll($str);
    }


    private function giveGp($player, $double = false)
    {
        $user_id = $player->user_id;
        $prev_gp = GuildPoints::getPreviousGP($user_id, $this->course_id);
        $earned_gp = round($player->race_stats->finish_time / 60 * count($this->player_array) / 4);

        // double with kong hat
        if ($double) {
            $earned_gp *= 2;
        }

        // limit gp gain to 10 per race
        if ($earned_gp > 10) {
            $earned_gp = 10;
        }

        // limit gp gain to 10 per course per day
        if ($prev_gp + $earned_gp > 10) {
            $earned_gp -= 10 - ($prev_gp - $earned_gp);
        }

        // increment gp
        if ($earned_gp >= 1) {
            GuildPoints::submit($user_id, $this->course_id, $earned_gp);
            $player->write("gpGain`$earned_gp");
        }
    }


    public function setPos($player, $data)
    {
        $this->sendToRoom('p'.$player->temp_id.'`'.$data, $player->user_id);
        list($moved_x, $moved_y) = explode('`', $data);
        $player->pos_x += $moved_x;
        $player->pos_y += $moved_y;
    }


    public function setExactPos($player, $data)
    {
        $this->sendToRoom('exactPos'.$player->temp_id.'`'.$data, $player->user_id);
        list($player->pos_x, $player->pos_y) = explode('`', $data);
    }


    public function squash($player, $data)
    {
        list($target_id, $x, $y) = explode('`', $data);
        $player->pos_x = (int)$x;
        $player->pos_y = (int)$y;
        $target = $this->idToPlayer($target_id);
        if (isset($target)
            && $player->wearingHat(Hats::JIGG)
            && $target->pos_y < $player->pos_y + 105
            && $target->pos_y > $player->pos_y + 0
            && $target->pos_x > $player->pos_x - 50
            && $target->pos_x < $player->pos_x + 50
        ) {
            $tempID = $player->temp_id;
            $posX = $target->pos_x;
            $posY = $target->pos_y - 90; // maybe fixes this: https://jiggmin2.com/forums/showthread.php?tid=1782
            $this->sendToRoom("exactPos$tempID`$posX`$posY", $player->user_id); // is this needed?
            $this->sendToRoom("squash$target->temp_id`", $player->user_id);
        }
    }


    public function sting($from, $target_id)
    {
        if ($target_id == $from->temp_id) {
            return; // this should never happen
        }
        $target = $this->idToPlayer($target_id);
        if (isset($target)
            && $from->wearingHat(Hats::JELLYFISH)
            && $target->pos_y < $from->pos_y + 75
            && $target->pos_y > $from->pos_y - 75
            && $target->pos_x > $from->pos_x - 75
            && $target->pos_x < $from->pos_x + 75
        ) {
            $this->sendToAll("sting$target->temp_id`$from->temp_id", $from->temp_id);
        }
    }


    private function isStillPlaying($temp_id)
    {
        $rs = $this->idToRaceStats($temp_id);
        return isset($rs) && $rs->still_here && !$rs->finished_race && !$rs->quit_race;
    }


    private function idToRaceStats($temp_id)
    {
        foreach ($this->finish_array as $rs) {
            if ($rs->temp_id == $temp_id) {
                return $rs;
            }
        }
        return null;
    }


    private function idToPlayer($temp_id)
    {
        foreach ($this->player_array as $player) {
            if ($player->temp_id == $temp_id) {
                return $player;
            }
        }
        return null;
    }


    public function setVar($player, $data)
    {
        if (!$player->race_stats->finished_race) {
            $this->sendToRoom('var'.$player->temp_id.'`'.$data, $player->user_id);

            if ($data === 'state`bumped' && $this->mode === self::MODE_DEATHMATCH) {
                $player->lives--;
                if ($player->lives <= 0) {
                    $this->finishRace($player);
                }
            }
            if (substr($data, 4) === 'item') {
                $player->items_used++;
            }
            $data = explode('`', $data);
            if ($data[0] === 'rot') {
                $player->rot = (int) $data[1];
            }
        }
    }


    public function sendChat($message, $user_id = -1)
    {
        // any added backticks will cut off the end of $text
        list($command, $name, $power, $text) = explode('`', $message);

        // send the message
        if ($command === 'chat' || ($command === 'systemChat' && $user_id === -1)) {
            foreach ($this->player_array as $player) {
                if (!$player->isIgnoredId($user_id)) {
                    $player->socket->write("$command`$name`$power`$text");
                }
            }
        }
    }


    private function startHatCountdown($player)
    {
        $secs = 5;
        if ($this->hatCountdownEnd === -1
            && $this->hasHats === -1
            && count($player->worn_hat_array) === count($this->finish_array)
            && $this->isStillPlaying($player->temp_id)
        ) {
            $this->hatCountdownEnd = $this->currentMS() + ($secs * 1000);
            $this->hasHats = $player->temp_id;

            $prospect = userify($player, $player->name, $player->group, $player->modPower());
            $msg = "If $prospect keeps all hats, they will finish in $secs seconds.<br><br>$secs";
            $this->sendToAll("systemChat`$msg");
            $this->sendToAll('startHatCountdown`');
        }
    }


    public function checkHatCountdown($player)
    {
        $prospect = $this->idToPlayer($this->hasHats);
        if (isset($prospect)
            && $this->hatCountdownEnd > $this->currentMS()
            && $this->isStillPlaying($prospect->temp_id)
            && count($prospect->worn_hat_array) === count($this->finish_array)
        ) {
            $secs_remaining = ceil(($this->hatCountdownEnd - $this->currentMS()) / 1000);
            $player->socket->write("systemChat`$secs_remaining");
            return;
        }
        $this->maybeEndHatCountdown();
    }


    private function cancelHatCountdown($msg = true)
    {
        if ($this->hasHats > -1) {
            $this->sendToAll('cancelHatCountdown`');
            if ($msg) {
                $rs = $this->idToRaceStats($this->hasHats);
                $prospect_url_name = userify($rs, $rs->name, $rs->group, $rs->mod_power);
                $msg = "$prospect_url_name dropped a hat!";
                $this->sendToAll("systemChat`$msg<br>");
            }
            $this->hatCountdownEnd = $this->hasHats = -1;
        }
    }


    public function maybeEndHatCountdown()
    {
        if ($this->currentMS() >= $this->hatCountdownEnd && $this->hasHats > -1) {
            $player = $this->idToPlayer($this->hasHats);
            if (isset($player) && $this->isStillPlaying($player->temp_id)) {
                $this->finishRace($player);
                $this->cancelHatCountdown(false);
                $winner = userify($player, $player->name, $player->group, $player->modPower());
                $this->sendToAll("systemChat`$winner finished!<br>");
            } else {
                $this->cancelHatCountdown();
            }
        }
    }

    private function loseAllHats($player)
    {
        foreach ($player->worn_hat_array as $hat) {
            $y = $player->pos_y - 50;
            $this->looseHat($player, "$player->pos_x`$y`$player->rot");
        }
    }


    public function grabEgg($player, $data)
    {
        if (!$player->race_stats->finished_race) {
            $player->race_stats->eggs++;
            $this->sendToRoom("removeEgg$data`", $player->user_id);
            $this->broadcastFinishTimes();
            $this->sendToAll('addEggs`1');
        }
    }


    public function looseHat($player, $info)
    {
        if (count($player->worn_hat_array) > 0) {
            $hat = array_pop($player->worn_hat_array);
            $this->loose_hat_array[$hat->id] = $hat;
            $this->sendToAll(
                'addEffect`Hat`'.$info.'`'.$hat->num.'`'.$hat->color.'`'.$hat->color2.'`'.$hat->id,
                $player->user_id
            );
            $this->sendToAll($this->getHatStr($player));
            if ($this->mode === self::MODE_HAT
                && $this->hasHats == $player->temp_id
                && $this->currentMS() < $this->hatCountdownEnd
            ) {
                $this->cancelHatCountdown();
            }
        }
    }


    public function objectiveReached($player, $data)
    {
        list($finish_id, $x, $y) = explode('`', $data);

        $this->verifyFinishPosition($x, $y, $finish_id);

        if (isset($player->race_stats->objectives_reached[$finish_id])) {
            throw new \Exception('This objective has already been reached.');
        }

        $player->race_stats->objectives_reached[$finish_id] = 1;
        $player->race_stats->last_objective_time = time();
        if (count($player->race_stats->objectives_reached) >= $this->finish_count) {
            $this->finishRace($player);
        }
    }


    private function verifyFinishPosition($x, $y, $id)
    {
        if (!is_numeric($id) || $id < 0 || $id > $this->finish_count) {
            throw new \Exception('finish id is out of range');
        }
        if ($this->finish_positions !== 'all' &&  is_array($this->finish_positions)) {
            $match = false;
            foreach ($this->finish_positions as $pos) {
                if ($id == $pos->id && $x == $pos->x && $y == $pos->y) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                throw new \Exception('No matching finish');
            }
        }
    }


    public function getHat($player, $hat_id)
    {
        $hat = @$this->loose_hat_array[$hat_id];
        if (isset($hat) && $this->isStillPlaying($player->temp_id)) {
            $this->loose_hat_array[$hat_id] = null;
            $this->sendToAll('removeHat'.$hat_id.'`');
            if ($hat->num == 12) {//thief hat
                $this->commitThievery($player, $hat);
            } elseif ($hat->num == Hats::ARTIFACT) {
                $this->assignArtifact($player, $hat);
            } else {
                $this->assignHat($player, $hat);
            }
        }
        if ($this->mode === self::MODE_HAT
            && count($player->worn_hat_array) === count($this->finish_array)
            && $this->hatCountdownEnd === -1
            && $this->hasHats === -1
        ) {
            $this->startHatCountdown($player);
        }
    }


    private function commitThievery($player, $hat)
    {
        $candidates = array();
        foreach ($this->player_array as $other_player) {
            if ($player !== $other_player) {
                if (count($other_player->worn_hat_array) > 0) {
                    array_push($candidates, $other_player);
                }
            }
        }
        if (count($candidates) > 0) {
            $index = array_rand($candidates);
            $target = $candidates[$index];
            $hat2 = array_pop($target->worn_hat_array);
            $this->assignHat($target, $hat);
            $this->assignHat($player, $hat2);
        } else {
            $this->assignHat($player, $hat);
        }
    }


    private function assignArtifact($player, $hat)
    {
        $this->loose_hat_array = array();
        foreach ($this->player_array as $other_player) {
            $other_player->worn_hat_array = array();
            $this->sendToAll($this->getHatStr($other_player));
        }
        $this->assignHat($player, $hat);
    }


    private function assignHat($player, $hat)
    {
        array_push($player->worn_hat_array, $hat);
        $this->sendToAll($this->getHatStr($player));
    }


    private function getHatStr($player)
    {
        $str = 'setHats'.$player->temp_id.'`';
        $len = count($player->worn_hat_array);
        for ($i = 0; $i < $len; $i++) {
            $hat = $player->worn_hat_array[$i];
            if ($i != 0) {
                $str .= '`';
            }
            $str .= $hat->num.'`'.$hat->color.'`'.$hat->color2;
        }
        return $str;
    }


    private function applyExpCurve($player, $exp)
    {
        if ($player->exp_today < 5000) {
            $tier = 2.0;
        } elseif ($player->exp_today < 25000) {
            $tier = 1.5;
        } else {
            $tier = 1;
        }
        $exp *= $tier;
        return $exp;
    }


    private function currentMS()
    {
        return microtime(true) * 1000;
    }


    public function remove()
    {
        foreach ($this as $key => $var) {
            if ($key !== 'mode' && $key !== 'finish_array') {
                $this->$key = null;
                unset($this->$key, $key, $var);
            }
        }

        parent::remove();
    }
}
