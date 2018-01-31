<?php

require_once(__DIR__ . '/../fns/artifact_first_check.php');

class Game extends Room {

	const LEVEL_BUTO = 1738847; //for jigg hat
	const LEVEL_DELIVERANCE = 1896157; //for slender set

	const MODE_RACE = 'race';
	const MODE_DEATHMATCH = 'deathmatch';
	const MODE_EGG = 'egg';
	const MODE_OBJECTIVE = 'objective';

	public static $artifact_level_id = 0;
	public static $artifact_x = 0;
	public static $artifact_y = 0;
	public static $artifact_updated_time = 0;

	private $finish_array = array();
	private $course_id;
	private $start_time;
	private $begun = false;
	private $loose_hat_array = array();
	private $next_hat_id = 0;
	private $prize;
	private $campaign;

	private $mode = self::MODE_RACE;
	private $level_hash = '';
	private $ending_egg = false;
	private $finish_count = 0;
	private $finish_positions = array();
	private $cowboy_chance = '';
	private $cowboy_mode = false;
	private $tournament = false;

	protected $room_name = 'game_room';
	protected $temp_id = 0;


	public function __construct($course_id){
		$this->course_id = $course_id;
		$this->tournament = pr2_server::$tournament;
		$this->start_time = microtime(true);
	}


	public function add_player($player){
		if(count($this->finish_array) < 4) {
			Room::add_player($player);
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



	public function remove_player($player){
		Room::remove_player($player);

		$this->finish_drawing($player);
		$player->race_stats->still_here = false;

		if(!isset($player->race_stats->finish_time)){
			$this->set_finish_time($player, 'forfeit');
		}
		else {
			$this->broadcast_finish_times();
		}

		$player->race_stats = NULL;
		$player->temp_id = NULL;
		unset($player->temp_id);

		if(count($this->player_array) <= 0){
			$this->remove();
		}
	}



	public function init(){

		$this->record_plays();
		$this->determine_prize();

		//send character info
		foreach($this->player_array as $player){
			$player->finished_race = false;
			$player->socket->write($player->get_local_info());
			$this->send_to_room($player->get_remote_info(), $player->user_id);
		}

		//super booster
		if( !$this->tournament ) {
			foreach($this->player_array as $player){
				if( $player->super_booster ) {
					$player->super_booster = false;
					$this->send_to_room( 'superBooster`' . $player->temp_id, -1 );
				}
			}
		}

		//happy hour
		if(HappyHour::isActive()) {
			$this->send_to_all('happyHour`');
		}

		//tournament
		if( $this->tournament ) {
			announce_tournament($this);
		}
	}



	private function record_plays() {
		global $play_count_array;
		$player_count = count($this->player_array);
		if(isset($play_count_array[$this->course_id])){
			$play_count_array[$this->course_id] += $player_count;
		}
		else{
			$play_count_array[$this->course_id] = $player_count;
		}
	}



	// Clint the Cowboy (Epic Cowboy Upgrade)
	private function is_clint_cowboy_here() {
		$ret = false;
		foreach($this->player_array as $player) {
			if($player->user_id == 5451130) {
				$ret = true;
			}
		}
		return($ret);
	}



	// Sir Sirlington (Epic Sir Parts + Epic Top Hat Upgrades)
	private function is_sir_sirlington_here() {
		$ret = false;
		foreach($this->player_array as $player) {
			if($player->user_id == 5321458) {
				$ret = true;
			}
		}
		return($ret);
	}



	private function determine_prize() {
		$player_count = count($this->player_array);

		global $campaign_array;
		if( isset($campaign_array[$this->course_id]) ) {
			$this->campaign = $campaign_array[$this->course_id];
		}

		if( isset($this->campaign) ) {
			$campaign_prize = Prizes::find( $this->campaign->prize_type, $this->campaign->prize_id );
			if( $player_count >= 4 || (isset($campaign_prize) && $campaign_prize->is_universal()) ) {
				$this->prize = $campaign_prize;
			}
		}

		if( $this->is_clint_cowboy_here() ) {
			$this->prize = Prizes::$EPIC_COWBOY_HAT;
		}

		if( $this->is_sir_sirlington_here() ) {
			$sir_prizes = array( Prizes::$EPIC_TOP_HAT, Prizes::$EPIC_SIR_HEAD, Prizes::$EPIC_SIR_BODY, Prizes::$EPIC_SIR_FEET );
			$this->prize = $sir_prizes[ array_rand($sir_prizes) ];
		}

		if( $this->course_id == self::LEVEL_DELIVERANCE ) {
			$slender_prizes = array( Prizes::$SLENDER_HEAD, Prizes::$SLENDER_BODY, Prizes::$SLENDER_FEET );
			$this->prize = $slender_prizes[ array_rand($slender_prizes) ];
		}

		if( !isset($this->prize) && $player_count >= 1 ) {
			if( rand($player_count*2, 20) >= 19 ) {
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

		if( !isset($this->prize) && $player_count >= 2){
			if(rand(0, 40) == 40){
				$this->prize = Prizes::$EXP_HAT;
			}
			if(rand(0, 45) == 45){
				$this->prize = Prizes::$SANTA_HAT;
			}
			if(rand(0, 50) == 50){
				$this->prize = Prizes::$PARTY_HAT;
			}
			if( rand(0, 40) == 40 && HappyHour::isActive() ) {
				$this->prize = Prizes::$JUMP_START_HAT;
			}
		}

		//no prizes on tournament
		if( pr2_server::$no_prizes ) {
			$this->prize = NULL;
		}

		//tell it to the world
		if( isset($this->prize) ) {
			$this->send_to_all( 'setPrize`'.$this->prize->to_str() );
		}
	}



	public function finish_drawing($player, $data=null) {
		if($player->race_stats->drawing === true) {
			$arr = explode('`', $data);
			$player->race_stats->drawing = false;
			if(isset($data)) {
				$rs = $player->race_stats;
				$rs->level_hash = $arr[0];
				$rs->mode = $arr[1];
				$rs->finish_positions = $arr[2];
				$rs->finish_count = $arr[3];
				$rs->cowboy_chance = $arr[4];
			}
			$this->send_to_all('finishDrawing`'.$player->temp_id);
		}
		$this->maybe_begin_race();
	}



	private function maybe_begin_race() {
		$begin_race = true;
		foreach($this->player_array as $player){
			if($player->race_stats->drawing === true){
				$begin_race = false;
				break;
			}
		}

		if($begin_race && !$this->begun) {
			$this->begin_race();
		}
	}


	private function begin_race() {
		if(!$this->begun ) {
			$this->begun = true;
			$this->mode = $this->democratize( 'mode' );
			$this->hash = $this->democratize( 'level_hash' );
			$this->finish_positions = $this->democratize( 'finish_positions' );
			$this->finish_count = $this->democratize( 'finish_count' );
			$this->cowboy_chance = $this->democratize( 'cowboy_chance' );

			//-- turn finish positions into an array
			if( $this->finish_positions != 'all' ) {
				$this->finish_positions = json_decode( $this->finish_positions );
			}

			//-- boot people with the wrong level hash
			foreach($this->player_array as $player) {
				if($this->hash !== $player->race_stats->level_hash) {
					$this->quit_race($player);
				}
			}

			//-- jigg hat
			if($this->course_id == self::LEVEL_BUTO) {
				$hat = new Hat($this->next_hat_id++, Hats::JIGG, 0xFFFFFF, -1);
				$this->loose_hat_array[$hat->id] = $hat;
				$x = 13450;
				$y = -6200;
				$rot = 0;
				$this->send_to_all("addEffect`Hat`$x`$y`$rot`$hat->num`$hat->color`$hat->color2`$hat->id", -1);
			}

			//-- artifact
			if( $this->course_id == self::$artifact_level_id ) {
				$hat = new Hat($this->next_hat_id++, Hats::ARTIFACT, 0xFFFFFF, -1);
				$this->loose_hat_array[$hat->id] = $hat;
				$x = self::$artifact_x;
				$y = self::$artifact_y;
				$rot = 0;
				$this->send_to_all("addEffect`Hat`$x`$y`$rot`$hat->num`$hat->color`$hat->color2`$hat->id", -1);
			}

			//-- eggs
			if( $this->mode == self::MODE_EGG ) {
				$this->send_to_all( 'setEggSeed`'.rand(0, 99999) );
				$this->send_to_all( 'addEggs`10' );
			}

			//-- sfchm
			if( $this->cowboy_chance === '' ) {
				$this->cowboy_chance = 5;
			}
			if( $this->tournament && $this->cowboy_chance != 100 ) {
				$this->cowboy_chance = 0;
			}
			if( !isset($this->campaign) && rand(1, 100) <= $this->cowboy_chance ){
				$this->cowboy_mode = true;
				$this->send_to_all('cowboyMode`');
			}

			//-- hats
			$this->init_hats();

			//-- start
			$this->start_time = microtime(true);
			$this->send_to_all( 'ping`' . time() );
			$this->send_to_all('beginRace`');
		}
	}



	private function init_hats() {
		foreach($this->player_array as $player) {
			$player->worn_hat_array = array();
			$hat_id = $player->hat;

			if( $this->tournament ) {
				$hat_id = pr2_server::$tournament_hat;
			}
			if( $this->cowboy_mode ) {
				$hat_id = 5;
			}

			if( $hat_id != 1 ) {
				$hat = new Hat( $this->next_hat_id++, $hat_id, $player->hat_color, $player->get_second_color('hat', $hat_id) );
				$player->worn_hat_array[0] = $hat;
			}

			$this->send_to_all($this->get_hat_str($player));
		}
	}



	private function democratize( $var ) {
		$winner = '';
		$candidates = array();
		foreach($this->player_array as $player) {
			$candidate = $player->race_stats->{$var};
			if($candidate != '') {
				if( !isset($candidates[$candidate]) ) {
					$candidates[$candidate] = 0;
				}
				$candidates[ $candidate ]++;
			}
		}
		arsort( $candidates );
		reset( $candidates );
		$winner = key( $candidates );
		return $winner;
	}


	public function remote_finish_race( $player, $data ) {
		if( $this->mode == self::MODE_RACE ) {
			list($finish_id, $x, $y) = explode('`', $data);
			$this->verify_finish_position( $x, $y, $finish_id );
		}
		$this->finish_race( $player );
	}


	public function finish_race($player) {
		if($player->finished_race === false && !isset($player->race_stats->finish_time) && $player->race_stats->drawing === false && $this->begun === true){


			$finish_time = microtime(true) - $this->start_time;
			$this->set_finish_time($player, $finish_time);

			$time_mod = $finish_time / 120;
			if($time_mod > 1) {
				$time_mod = 1;
			}

			$place = array_search($player->race_stats, $this->finish_array);

			if( $place == 0 && count($this->finish_array) > 1 && $finish_time > 10 ) {
				$this->give_gp( $player );
				if( pr2_server::$tournament ) {
					$this->broadcast_results( $player );
				}
			}

			//--- prize -----------
			$prize = null;

			if( $this->course_id == self::LEVEL_BUTO && $player->wearing_hat(Hats::JIGG) ) {
				$prize = Prizes::$JIGG_HAT;
			}
			if( isset($this->prize) && ($place == 0 || $this->prize->is_universal()) ) {
				$prize = $this->prize;
			}

			if( isset($prize) ) {
				$autoset = ( $prize->get_type() == 'hat' );
				$result = $player->gain_part( $prize->get_type(), $prize->get_id(), $autoset );
				if( !$result ) {
					$prize = Prizes::$EXP_5;
				}
				$player->write( 'winPrize`' . $prize->to_str() );
			}


			//--- exp gain -------
			$tot_exp_gain = 0;
			$tot_lux_gain = 0;
			$wearing_moon = false;

			//--- welcome back bonus ---
			$welcome_back_bonus = 0;
			if( $player->exp_today == 0 && $player->rank >= 5 ) {
				$welcome_back_bonus = 1000;
			}

			//--- level bonus ---
			else {
				$level_bonus = $this->apply_exp_curve( $player, 25 * $time_mod );

				$completed_perc = 0;
				if( $this->mode == self::MODE_OBJECTIVE && $this->finish_count > 0 ) {
					$objective_count = count( $player->race_stats->objectives_reached );
					if( $objective_count > $this->finish_count ) {
						$objective_count = $this->finish_count;
					}
					$completed_perc = $objective_count / $this->finish_count;
					$level_bonus *= $completed_perc;
				}

				$level_bonus = round( $level_bonus );

				if( pr2_server::$no_prizes ) {
					$level_bonus = 0;
				}

				if($this->mode == self::MODE_DEATHMATCH) {
					$player->write('award`Survival Bonus`+ '.$level_bonus);
				}
				else if ( $this->mode == self::MODE_OBJECTIVE && $completed_perc < 1 ) {
					$player->write('award`Level Attempted`+ '.$level_bonus);
				}
				else {
					$player->write('award`Level Completed`+ '.$level_bonus);
				}

				$tot_exp_gain += $level_bonus;
			}

			//--- opponent bonus ---
			for($i = $place+1; $i < count($this->finish_array); $i++){
				$race_stats = $this->finish_array[$i];
				$exp_gain = ($race_stats->rank+5) * $time_mod;
				/*if($race_stats->ip == $player->ip) {
					$exp_gain /= 2;
				}*/
				$exp_gain = ceil( $this->apply_exp_curve( $player, $exp_gain ) );
				if( pr2_server::$no_prizes ) {
					$exp_gain = 0;
				}
				$tot_exp_gain += $exp_gain;
				$player->write('award`Defeated '.$race_stats->name.'`+ '.$exp_gain);
			}

			$hat_bonus = 0;
			foreach($player->worn_hat_array as $hat) {
				if($hat->num == 2){
					$hat_bonus += 1;
				}
				else if($hat->num == 3){
					$hat_bonus += .25;
				}
				else if($hat->num == 11){
					$wearing_moon = true;
				}
			}
			if($hat_bonus > 0){
				$tot_exp_gain += $tot_exp_gain * $hat_bonus;
				$player->write('award`Hat Bonus`exp X '.($hat_bonus+1));
			}

			if( isset($prize) && $prize->get_type() == Prizes::TYPE_EXP ){
				$tot_exp_gain += $prize->get_id();
			}

			if($finish_time >= 90 && $wearing_moon) {
				$tot_lux_gain = count($this->finish_array) - $place - 1;
			}

			if(HappyHour::isActive()) {
				$tot_exp_gain *= 2;
				$tot_lux_gain *= 2;
			}

			//apply welcome back bonus after all multipliers
			if( $welcome_back_bonus > 0 ) {
				$tot_exp_gain += $welcome_back_bonus;
				$player->write('award`Welcome Back Bonus`+ 1,000');
			}

			//artifact bonus
			if( $this->course_id == self::$artifact_level_id && $player->artifact == 0 && $player->wearing_hat(Hats::ARTIFACT) ) {
				$player->artifact = 1;

				$max_artifact_bonus = 11111;
				$artifact_bonus = $max_artifact_bonus * $player->active_rank / 20;
				if( $artifact_bonus > $max_artifact_bonus ) {
					$artifact_bonus = $max_artifact_bonus;
				}

				$tot_exp_gain += $artifact_bonus;
				$player->write( 'award`Artifact Found!`+ ' . number_format( $artifact_bonus ) );

				global $port;
				artifact_first_check($port, $player);
			}

			//---
			$player->inc_exp($tot_exp_gain);

			//lux gain
			if($tot_lux_gain > 0) {
				$player->write('setLuxGain`'.$tot_lux_gain);
				$player->lux += $tot_lux_gain;
			}

			//--- save
			if( isset($prize) && $prize->get_type() != Prizes::TYPE_EXP ) {
				$player->save_info();
			}
			else {
				$player->maybe_save();
			}
		}
		else {
			$this->set_finish_time($player, 'forfeit');
		}

		$player->finished_race = true;

		// everyone finishes at the same time in egg mode
		$this->maybe_end_egg();
	}



	private function broadcast_results( $player ) {
		global $chat_room_array;

		if( isset($chat_room_array['main']) ) {
			$main = $chat_room_array['main'];
			$message = '';
			$names = array();
			foreach($this->finish_array as $race_stats) {
				$names[] = "[$race_stats->name]";
			}
			$vs_names = join( ' vs ', $names );
			$message = "$vs_names: // $player->name wins!";
			$main->send_chat( "systemChat`$message", -1 );
		}
	}



	private function maybe_end_deathmatch() {
		if($this->mode == self::MODE_DEATHMATCH) {
			$unfinished = 0;
			foreach($this->finish_array as $race_stats) {
				if( !isset($race_stats->finish_time) ) {
					$unfinished++;
				}
			}
			if($unfinished === 1) {
				foreach($this->player_array as $player) {
					if( !$player->finished_race && !isset($player->auto_win_deathmatch) ) {
						$last_player = $player;
					}
				}
				if(isset($last_player)) {
					$last_player->auto_win_deathmatch = true;
					$this->start_time -= 1;
					$this->finish_race($last_player);
					unset($last_player->auto_win_deathmatch);
				}
			}
		}
	}



	private function maybe_end_egg() {
		if( $this->mode === self::MODE_EGG && !$this->ending_egg) {
			$someone_finished = false;
			$everyone_quit = true;

			foreach($this->player_array as $player) {
				if( $player->finished_race ) {
					$someone_finished = true;
					break;
				}
			}

			foreach($this->player_array as $player) {
				if( !$player->quit_race ) {
					$everyone_quit = false;
					break;
				}
			}

			if( $someone_finished || $everyone_quit ) {
				$this->ending_egg = true;
				foreach($this->player_array as $player) {
					if( !$player->finished_race ) {
						$this->finish_race($player);
					}
				}
			}
		}
	}



	public function quit_race($player) {
		$this->finish_drawing($player);
		if($player->finished_race == false) {
			$player->quit_race = true;
			if($this->mode == self::MODE_DEATHMATCH && $this->begun) {
				$this->finish_race( $player );
			}
			else if( $this->mode == self::MODE_OBJECTIVE && $this->begun ) {
				$this->finish_race( $player );
			}
			else if( $this->mode === self::MODE_EGG ) {
				$this->maybe_end_egg();
			}
			else if( $this->mode === self::MODE_RACE ) {
				$player->finished_race = true;
				$this->set_finish_time($player, 'forfeit');
			}
		}
	}



	private function set_finish_time($player, $finish_time){
		if(!isset($player->race_stats->finish_time)) {
			$player->race_stats->finish_time = $finish_time;
		}
		if($this->mode === self::MODE_DEATHMATCH) {
			$sort_func = 'sort_finish_array_deathmatch';
		}
		else if( $this->mode === self::MODE_EGG) {
			$sort_func = 'sort_finish_array_egg';
		}
		else if( $this->mode === self::MODE_OBJECTIVE ) {
			$sort_func = 'sort_finish_array_objective';
		}
		else {
			$sort_func = 'sort_finish_array';
		}
		usort($this->finish_array, $sort_func);

		$this->broadcast_finish_times();

		$this->send_to_all('var'.$player->temp_id.'`beginRemove`1');
		$this->maybe_end_deathmatch();
	}



	private function broadcast_finish_times() {
		$str = 'finishTimes';
		foreach($this->finish_array as $race_stats) {
			if($this->mode === self::MODE_EGG) {
				$finish_time = $race_stats->eggs;
			}
			else {
				$finish_time = $race_stats->finish_time;
			}
			if(isset($finish_time)) {
				$str .= '`'.$race_stats->name.'`'.$finish_time.'`'.$race_stats->still_here;
			}
		}
		$this->send_to_all($str);
	}



	private function give_gp( $player ) {
		$user_id = $player->user_id;
		$prev_gp = GuildPoints::get_previous_gp( $user_id, $this->course_id );
		$earned_gp = floor( $player->race_stats->finish_time / 60 * count($this->player_array) / 4 );
		if( $prev_gp + $earned_gp > 10 ) {
			$earned_gp -= ( $prev_gp + $earned_gp ) - 10;
		}
		if( $earned_gp >= 1 ) {
			GuildPoints::submit( $user_id, $this->course_id, $earned_gp );
			$player->write( "gpGain`$earned_gp" );
		}
	}



	public function set_pos($player, $data){
		$this->send_to_room('p'.$player->temp_id.'`'.$data, $player->user_id);
		list($moved_x, $moved_y) = explode('`', $data);
		$player->pos_x += $moved_x;
		$player->pos_y += $moved_y;
	}



	public function set_exact_pos($player, $data){
		$this->send_to_room('exactPos'.$player->temp_id.'`'.$data, $player->user_id);
		list($player->pos_x, $player->pos_y) = explode('`', $data);
	}



	public function squash($player, $data) {
		list($target_id, $x, $y) = explode('`', $data);
		$player->pos_x = (int)$x;
		$player->pos_y = (int)$y;
		$target = $this->id_to_player($target_id);
		if(isset($target)
				&& $target->pos_y < $player->pos_y + 105
				&& $target->pos_y > $player->pos_y + 0
				&& $target->pos_x > $player->pos_x - 50
				&& $target->pos_x < $player->pos_x + 50) {
			$this->send_to_room('exactPos'.$player->temp_id.'`'.$target->pos_x.'`'.$target->pos_y-40, $player->user_id);
			$this->send_to_room('squash'.$target->temp_id.'`', $player->user_id);
		}
	}



	private function id_to_player($temp_id) {
		$player = null;
		foreach($this->player_array as $other_player) {
			if($other_player->temp_id == $temp_id) {
				$player = $other_player;
				break;
			}
		}
		return $player;
	}



	public function set_var($player, $data) {
		if(!$player->finished_race) {
			$this->send_to_room('var'.$player->temp_id.'`'.$data, $player->user_id);

			if($data === 'state`bumped' && $this->mode === self::MODE_DEATHMATCH) {
				$player->lives--;
				if($player->lives <= 0) {
					$this->finish_race($player);
				}
			}
			if(substr($data, 4) === 'item') {
				$player->items_used++;
			}
		}
	}



	public function send_chat($message, $user_id){
		foreach($this->player_array as $player){
			if(!$player->is_ignored_id($user_id)){
				$player->socket->write($message);
			}
		}
	}



	public function grab_egg( $player, $data ) {
		if( !$player->finished_race ) {
			$player->race_stats->eggs++;
			$this->send_to_room( "removeEgg$data`", $player->user_id );
			//$this->send_to_all( 'eggCount`'.$player->temp_id.'`'.$player->race_stats->eggs );
			$this->broadcast_finish_times();
			$this->send_to_all( 'addEggs`1' );
		}
	}


	public function loose_hat($player, $info){
		if(count($player->worn_hat_array) > 0){
			$hat = array_pop($player->worn_hat_array);
			$this->loose_hat_array[$hat->id] = $hat;
			$this->send_to_all('addEffect`Hat`'.$info.'`'.$hat->num.'`'.$hat->color.'`'.$hat->color2.'`'.$hat->id, $player->user_id);
			$this->send_to_all($this->get_hat_str($player));
		}
	}


	public function objective_reached( $player, $data ) {
		list($finish_id, $x, $y) = explode('`', $data);

		$this->verify_finish_position( $x, $y, $finish_id );

		if( isset( $player->race_stats->objectives_reached[$finish_id] ) ) {
			throw new Exception( 'This objective has already been reached.' );
		}

		$player->race_stats->objectives_reached[$finish_id] = 1;
		$player->race_stats->last_objective_time = time();
		if( count( $player->race_stats->objectives_reached ) >= $this->finish_count ) {
			$this->finish_race( $player );
		}
	}


	private function verify_finish_position( $x, $y, $id ) {
		if( !is_numeric($id) || $id < 0 || $id > $this->finish_count ) {
			throw new Exception( 'finish id is out of range' );
		}
		if( $this->finish_positions !== 'all' &&  is_array( $this->finish_positions ) ) {
			$match = false;
			foreach( $this->finish_positions as $pos ) {
				if( $id == $pos->id && $x == $pos->x && $y == $pos->y ) {
					$match = true;
					break;
				}
			}
			if( !$match ) {
				throw new Exception( 'No matching finish' );
			}
		}
	}


	public function get_hat($player, $hat_id) {
		$hat = @$this->loose_hat_array[$hat_id];
		if(isset($hat)) {
			$this->loose_hat_array[$hat_id] = NULL;
			$this->send_to_all('removeHat'.$hat_id.'`');
			if($hat->num == 12) {//thief hat
				$this->commit_thievery($player, $hat);
			}
			else if( $hat->num == Hats::ARTIFACT ) {
				$this->assign_artifact( $player, $hat );
			}
			else {
				$this->assign_hat($player, $hat);
			}
		}
	}


	private function commit_thievery($player, $hat) {
		$candidates = array();
		foreach($this->player_array as $other_player) {
			if($player !== $other_player) {
				if(count($other_player->worn_hat_array) > 0) {
					array_push($candidates, $other_player);
				}
			}
		}
		if(count($candidates) > 0) {
			$index = array_rand($candidates);
			$target = $candidates[$index];
			$hat2 = array_pop($target->worn_hat_array);
			$this->assign_hat($target, $hat);
			$this->assign_hat($player, $hat2);
		}
		else {
			$this->assign_hat($player, $hat);
		}
	}


	private function assign_artifact( $player, $hat ) {
		$this->loose_hat_array = array();
		foreach($this->player_array as $other_player) {
			$other_player->worn_hat_array = array();
			$this->send_to_all($this->get_hat_str($other_player));
		}
		$this->assign_hat( $player, $hat );
	}


	private function assign_hat($player, $hat) {
		array_push($player->worn_hat_array, $hat);
		$this->send_to_all($this->get_hat_str($player));
	}


	private function get_hat_str($player){
		$str = 'setHats'.$player->temp_id.'`';
		$len = count($player->worn_hat_array);
		for($i = 0; $i < $len; $i++){
			$hat = $player->worn_hat_array[$i];
			if($i != 0){
				$str .= '`';
			}
			$str .= $hat->num.'`'.$hat->color.'`'.$hat->color2;
		}
		return $str;
	}


	private function apply_exp_curve( $player, $exp ) {
		if( $player->exp_today < 5000 )
			$tier = 2.0;
		else if( $player->exp_today < 25000 )
			$tier = 1.5;
		else
			$tier = 1;
		$exp *= $tier;
		return $exp;
	}


	public function remove(){
		$this->finish_array = NULL;
		$this->course_id = NULL;
		$this->start_time = NULL;
		$this->begun = NULL;
		$this->loose_hat_array = NULL;
		$this->next_hat_id = NULL;
		$this->prize = NULL;
		$this->campaign = NULL;
		$this->room_name = NULL;
		$this->temp_id = NULL;

		parent::remove();
	}
}
?>
