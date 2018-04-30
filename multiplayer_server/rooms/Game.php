<?php

namespace pr2\multi;

class Game extends Room
{
    const LEVEL_COMPASS = 3236908; // for top hat
    const LEVEL_BUTO = 1738847; // for jigg hat
    const LEVEL_DELIVERANCE = 1896157; // for slender set

    const MODE_RACE = 'race';
    const MODE_DEATHMATCH = 'deathmatch';
    const MODE_EGG = 'egg';
    const MODE_OBJECTIVE = 'objective';

    const PLAYER_SIR = 5321458; // sir sirlington
    const PLAYER_CLINT = 5451130; // clint the cowboy

    private $finish_array = array();
    private $course_id;
    private $start_time;
    private $begun = false;
    private $loose_hat_array = array();
    private $next_hat_id = 0;
    private $prize;
    private $campaign;

    private $mode = self::MODE_RACE;
    private $ending_egg = false;
    private $finish_count = 0;
    private $finish_positions = array();
    private $cowboy_chance = '';
    private $cowboy_mode = false;
    private $tournament = false;

    protected $room_name = 'game_room';
    protected $temp_id = 0;


    public function __construct($course_id)
    {
        $this->course_id = $course_id;
        $this->tournament = PR2SocketServer::$tournament;
        $this->start_time = microtime(true);
    }


    public function addPlayer($player)
    {
        if (count($this->finish_array) < 4) {
            Room::addPlayer($player);
            $player->socket->write('startGame`'.$this->course_id);
            $player->temp_id = $this->temp_id;
            $player->pos_x = 0;
            $player->pos_y = 0;
            $player->average_vel_x = 0;
            $player->average_vel_y = 0;
            $player->lives = 3;
            $player->finished_race = false;
            $player->quit_race = false;
            $this->temp_id++;
            $race_stats = new RaceStats($player->temp_id, $player->name, $player->active_rank, $player->ip);
            array_push($this->finish_array, $race_stats);
            $player->race_stats = $race_stats;
        }
    }



    public function removePlayer($player)
    {
        Room::removePlayer($player);

        $this->finishDrawing($player);
        $player->race_stats->still_here = false;

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
            $player->finished_race = false;
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
        foreach ($this->player_array as $player) {
            $level_id = $this->course_id;
            $user_id = $player->user_id;
            rate_limit("level-play-count-$level_id-$user_id", 3600, 50);
        }
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

        // Sir Sirlington; Awards: Epic Sir Set + Epic Top Hat
        if ($this->isPlayerHere(self::PLAYER_SIR)) {
            $sir_prizes = [
                Prizes::$EPIC_TOP_HAT,
                Prizes::$EPIC_SIR_HEAD,
                Prizes::$EPIC_SIR_BODY,
                Prizes::$EPIC_SIR_FEET
            ];
            $this->prize = $sir_prizes[ array_rand($sir_prizes) ];
        }

        // Clint the Cowboy; Awards: Epic Cowboy Hat
        if ($this->isPlayerHere(self::PLAYER_CLINT)) {
            $this->prize = Prizes::$EPIC_COWBOY_HAT;
        }

        // -Deliverance- by changelings; Awards: Slender Set
        if ($this->course_id == self::LEVEL_DELIVERANCE) {
            $slender_prizes = array( Prizes::$SLENDER_HEAD, Prizes::$SLENDER_BODY, Prizes::$SLENDER_FEET );
            $this->prize = $slender_prizes[ array_rand($slender_prizes) ];
        }

        // The Golden Compass by -Shadowfax-; Awards: Top Hat
        if ($this->course_id == self::LEVEL_COMPASS) {
            $this->prize = Prizes::$TOP_HAT;
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

            // boot people with the wrong level hash
            foreach ($this->player_array as $player) {
                if ($this->hash !== $player->race_stats->level_hash) {
                    $this->quitRace($player);
                }
            }

            // jigg hat
            if ($this->course_id == self::LEVEL_BUTO) {
                $hat = new Hat($this->next_hat_id++, Hats::JIGG, 0xFFFFFF, -1);
                $this->loose_hat_array[$hat->id] = $hat;
                $x = 13450;
                $y = -6200;
                $rot = 0;
                $this->sendToAll("addEffect`Hat`$x`$y`$rot`$hat->num`$hat->color`$hat->color2`$hat->id", -1);
            }

            // place artifact hat
            if ($this->course_id == Artifact::$level_id) {
                $hat = new Hat($this->next_hat_id++, Hats::ARTIFACT, 0xFFFFFF, -1);
                $this->loose_hat_array[$hat->id] = $hat;
                $x = Artifact::$x;
                $y = Artifact::$y;
                $rot = 0;
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
        }
    }



    private function initHats()
    {
        foreach ($this->player_array as $player) {
            $player->worn_hat_array = array();
            $hat_id = $player->hat;

            if ($this->tournament) {
                $hat_id = PR2SocketServer::$tournament_hat;
            }
            if ($this->cowboy_mode) {
                $hat_id = 5;
            }

            if ($hat_id != 1) {
                $hat = new Hat(
                    $this->next_hat_id++,
                    $hat_id,
                    $player->hat_color,
                    $player->getSecondColor('hat', $hat_id)
                );
                $player->worn_hat_array[0] = $hat;
            }

            $this->sendToAll($this->getHatStr($player));
        }
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
        if ($this->mode == self::MODE_RACE) {
            list($finish_id, $x, $y) = explode('`', $data);
            $this->verifyFinishPosition($x, $y, $finish_id);
        }
        $this->finishRace($player);
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
        global $pdo;

        if ($player->finished_race === false
            && !isset($player->race_stats->finish_time)
            && $player->race_stats->drawing === false
            && $this->begun === true
        ) {
            $finish_microtime = microtime(true);
            $full_time = $finish_microtime - $this->start_time;

            // get/format/validate/set finish time
            $finish_time = $this->timeFormat($full_time);
            $broadcast_time = $this->timeFormat($full_time, 3);
            if ($finish_time > 31536000) {
                $finish_time = 0; // if the race time > 1 year, set it to 0
            }
            $this->setFinishTime($player, $finish_time);

            $time_mod = $finish_time / 120;
            if ($time_mod > 1) {
                $time_mod = 1;
            }

            $place = array_search($player->race_stats, $this->finish_array);

            // announce on tournament server
            if ($place == 0 && count($this->finish_array) > 1 && $finish_time > 10 && PR2SocketServer::$tournament) {
                $this->broadcastResults($player, $broadcast_time);
            }

            //--- prize -----------
            $prize = null;

            if ($this->course_id == self::LEVEL_BUTO && $player->wearingHat(Hats::JIGG)) {
                $prize = Prizes::$JIGG_HAT;
            }
            if (isset($this->prize) && ($place == 0 || $this->prize->isUniversal())) {
                $prize = $this->prize;
            }

            if (isset($prize)) {
                $autoset = ( $prize->getType() == 'hat' );
                $result = $player->gainPart($prize->getType(), $prize->getId(), $autoset);
                if ($result == true) {
                    $player->write('winPrize`' . $prize->toStr());
                }
            }


            //--- exp gain -------
            $tot_exp_gain = 0;

            //--- welcome back bonus ---
            $welcome_back_bonus = 0;
            if ($player->exp_today == 0 && $player->rank >= 5) {
                $welcome_back_bonus = 1000;
            } //--- level bonus ---
            else {
                $level_bonus = $this->appyExpCurve($player, 25 * $time_mod);

                $completed_perc = 0;
                if ($this->mode == self::MODE_OBJECTIVE && $this->finish_count > 0) {
                    $objective_count = count($player->race_stats->objectives_reached);
                    if ($objective_count > $this->finish_count) {
                        $objective_count = $this->finish_count;
                    }
                    $completed_perc = $objective_count / $this->finish_count;
                    $level_bonus *= $completed_perc;
                }

                $level_bonus = round($level_bonus);

                if (PR2SocketServer::$no_prizes) {
                    $level_bonus = 0;
                }

                if ($this->mode == self::MODE_DEATHMATCH) {
                    $player->write('award`Survival Bonus`+ '.$level_bonus);
                } elseif ($this->mode == self::MODE_OBJECTIVE && $completed_perc < 1) {
                    $player->write('award`Level Attempted`+ '.$level_bonus);
                } else {
                    $player->write('award`Level Completed`+ '.$level_bonus);
                }

                $tot_exp_gain += $level_bonus;
            }

            //--- opponent bonus ---
            for ($i = $place+1; $i < count($this->finish_array); $i++) {
                $race_stats = $this->finish_array[$i];
                $exp_gain = ($race_stats->rank+5) * $time_mod;
                $exp_gain = ceil($this->appyExpCurve($player, $exp_gain));
                if (PR2SocketServer::$no_prizes) {
                    $exp_gain = 0;
                }
                $tot_exp_gain += $exp_gain;
                $player->write('award`Defeated '.$race_stats->name.'`+ '.$exp_gain);
            }

            // hat, campaign, hh bonuses
            $hat_bonus = 0;
            $gp_multiplier = 1;
            $wearing_moon = false;
            foreach ($player->worn_hat_array as $hat) {
                if ($hat->num == 2) {
                    if (isset($this->campaign)) {
                        $hat_bonus += .4;
                    } elseif (!isset($this->campaign)) {
                        $hat_bonus += 1;
                    }
                } elseif ($hat->num == 3) {
                    $hat_bonus += .25;
                } elseif ($hat->num == 11) {
                    $wearing_moon = true;
                    $gp_multiplier += 1;
                }
            }
            if ($hat_bonus > 0) {
                $tot_exp_gain += $tot_exp_gain * $hat_bonus;
            }

            // gp bonus
            if (($place == 0 || $wearing_moon === true) && count($this->finish_array) > 1 && $finish_time > 10) {
                $this->giveGp($player, $wearing_moon, $gp_multiplier);
            }

            // happy hour bonus
            if (HappyHour::isActive()) {
                $tot_exp_gain *= 2;
            }

            // campaign bonus
            if (isset($this->campaign)) {
                $tot_exp_gain *= 2;
            }

            // tell the user about the hat/hh/campaign bonus(es)
            if ($hat_bonus > 0 || isset($this->campaign) || HappyHour::isActive() == true) {
                // hat bonus only
                if ($hat_bonus > 0 && !isset($this->campaign) && HappyHour::isActive() != true) {
                    $player->write('award`Hat Bonus`exp X '.($hat_bonus+1));
                } // campaign bonus only
                elseif ($hat_bonus == 0 && isset($this->campaign) && HappyHour::isActive() != true) {
                    $player->write('award`Campaign Bonus`exp X 2');
                } // hh bonus only
                elseif ($hat_bonus == 0 && !isset($this->campaign) && HappyHour::isActive() == true) {
                    $player->write('award`Happy Hour Bonus`exp X 2');
                } // hat + campaign bonuses
                elseif ($hat_bonus > 0 && isset($this->campaign) && HappyHour::isActive() != true) {
                    $player->write('award`Hat & Campaign Bonuses`exp X '.($hat_bonus+2));
                } // hat + hh bonuses
                elseif ($hat_bonus > 0 && !isset($this->campaign) && HappyHour::isActive() == true) {
                    $player->write('award`Hat/Happy Hour Bonuses`exp X '.($hat_bonus+2));
                } // hh + campaign bonuses
                elseif ($hat_bonus == 0 && isset($this->campaign) && HappyHour::isActive() == true) {
                    $player->write('award`Campaign/HH Bonuses`exp X 4');
                } // hat+ campaign + hh bonuses
                elseif ($hat_bonus > 0 && isset($this->campaign) && HappyHour::isActive() == true) {
                    $player->write('award`Hat/Campaign/HH Bonuses`exp X '.($hat_bonus+4));
                }
            }

            //apply welcome back bonus after all multipliers
            if ($welcome_back_bonus > 0) {
                $tot_exp_gain += $welcome_back_bonus;
                $player->write('award`Welcome Back Bonus`+ 1,000');
            }

            //artifact bonus
            if ($this->course_id == Artifact::$level_id && $player->wearingHat(Hats::ARTIFACT)) {
                $result = save_finder($pdo, $player);
                if ($result) {
                    $max_artifact_bonus = 50000;
                    $artifact_bonus = $max_artifact_bonus * $player->active_rank / 60;
                    if ($artifact_bonus > $max_artifact_bonus) {
                        $artifact_bonus = $max_artifact_bonus;
                    }

                    $tot_exp_gain += $artifact_bonus;
                    $player->write('award`Artifact Found!`+ ' . number_format($artifact_bonus));
                }
            }
            if ($tot_exp_gain > 100000) {
                $tot_exp_gain = 0; //resets unrealistic exp
            }
            
            //---
            $player->incExp($tot_exp_gain);

            //--- save
            $player->maybeSave();
        } else {
            $this->setFinishTime($player, 'forfeit');
        }

        $player->finished_race = true;

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
            foreach ($this->finish_array as $race_stats) {
                $names[] = "[$race_stats->name]";
            }
            $vs_names = join(' vs ', $names);
            $html_name = htmlspecialchars($player->name);
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
                    if (!$player->finished_race && !isset($player->auto_win_deathmatch)) {
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
                if ($player->finished_race) {
                    $someone_finished = true;
                    break;
                }
            }

            foreach ($this->player_array as $player) {
                if (!$player->quit_race) {
                    $everyone_quit = false;
                    break;
                }
            }

            if ($someone_finished || $everyone_quit) {
                $this->ending_egg = true;
                foreach ($this->player_array as $player) {
                    if (!$player->finished_race) {
                        $this->finishRace($player);
                    }
                }
            }
        }
    }



    public function quitRace($player)
    {
        $this->finishDrawing($player);
        if ($player->finished_race == false) {
            $player->quit_race = true;
            if ($this->mode == self::MODE_DEATHMATCH && $this->begun) {
                $this->finishRace($player);
            } elseif ($this->mode == self::MODE_OBJECTIVE && $this->begun) {
                $this->finishRace($player);
            } elseif ($this->mode === self::MODE_EGG) {
                $this->maybeEndEgg();
            } elseif ($this->mode === self::MODE_RACE) {
                $player->finished_race = true;
                $this->setFinishTime($player, 'forfeit');
            }
        }
    }



    private function setFinishTime($player, $finish_time)
    {
        if (!isset($player->race_stats->finish_time)) {
            $player->race_stats->finish_time = $finish_time;
        }
        if ($this->mode === self::MODE_DEATHMATCH) {
            $sort_func = 'sort_finish_array_deathmatch';
        } elseif ($this->mode === self::MODE_EGG) {
            $sort_func = 'sort_finish_array_egg';
        } elseif ($this->mode === self::MODE_OBJECTIVE) {
            $sort_func = 'sort_finish_array_objective';
        } else {
            $sort_func = 'sort_finish_array';
        }
        usort($this->finish_array, $sort_func);

        $this->broadcastFinishTimes();

        $this->sendToAll('var'.$player->temp_id.'`beginRemove`1');
        $this->maybeEndDeathmatch();
    }



    private function broadcastFinishTimes()
    {
        $str = 'finishTimes';
        foreach ($this->finish_array as $race_stats) {
            if ($this->mode === self::MODE_EGG) {
                $finish_time = $race_stats->eggs;
            } else {
                $finish_time = $race_stats->finish_time;
            }
            if (isset($finish_time)) {
                $str .= '`'.$race_stats->name.'`'.$finish_time.'`'.$race_stats->still_here;
            }
        }
        $this->sendToAll($str);
    }



    private function giveGp($player, $wearing_moon = false, $multiplier = 1)
    {
        $user_id = $player->user_id;
        $prev_gp = GuildPoints::getPreviousGP($user_id, $this->course_id);
        $earned_gp = floor($player->race_stats->finish_time / 60 * count($this->player_array) / 4) * $multiplier;
        if ($earned_gp <= 0 && $wearing_moon === true) {
            $earned_gp += 1; // give at least 1 lux for moon hat users
        }
        if ($prev_gp + $earned_gp > 10 && $wearing_moon === false) {
            $earned_gp -= ( $prev_gp + $earned_gp ) - 10; // limit non-moon hat gp to 10
        } elseif ($prev_gp + $earned_gp > 20 && $wearing_moon === true) {
            $earned_gp -= ( $prev_gp + $earned_gp ) - 20; // limit moon hat gp to 20
        }
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
            && $target->pos_y < $player->pos_y + 105
            && $target->pos_y > $player->pos_y + 0
            && $target->pos_x > $player->pos_x - 50
            && $target->pos_x < $player->pos_x + 50
        ) {
            $this->sendToRoom('exactPos'.$player->temp_id.'`'.$target->pos_x.'`'.$target->pos_y-40, $player->user_id);
            $this->sendToRoom('squash'.$target->temp_id.'`', $player->user_id);
        }
    }



    private function idToPlayer($temp_id)
    {
        $player = null;
        foreach ($this->player_array as $other_player) {
            if ($other_player->temp_id == $temp_id) {
                $player = $other_player;
                break;
            }
        }
        return $player;
    }



    public function setVar($player, $data)
    {
        if (!$player->finished_race) {
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
        }
    }


    public function sendChat($message, $user_id)
    {
        // any added backticks will cut off the end of $text
        list($command, $name, $power, $text) = explode('`', $message);

        // send the message
        if ($command === 'chat') {
            foreach ($this->player_array as $player) {
                if (!$player->isIgnoredId($user_id)) {
                    $player->socket->write("$command`$name`$power`$text");
                }
            }
        }
    }



    public function grabEgg($player, $data)
    {
        if (!$player->finished_race) {
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
        if (isset($hat)) {
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


    private function appyExpCurve($player, $exp)
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


    public function remove()
    {
        $this->finish_array = null;
        $this->course_id = null;
        $this->start_time = null;
        $this->begun = null;
        $this->loose_hat_array = null;
        $this->next_hat_id = null;
        $this->prize = null;
        $this->campaign = null;
        $this->room_name = null;
        $this->temp_id = null;

        parent::remove();
    }
}
