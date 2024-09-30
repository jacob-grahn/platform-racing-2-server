<?php


// -- CRON -- \\


function run_update_cycle($pdo)
{
    output("Running update cycle...\n");

    // recent bans
    $bans_to_send = [];
    $recent_bans = bans_select_recently_modified($pdo);
    foreach ($recent_bans as $ban) {
        $ban_id = $ban_time = $expire_time = 0;
        $lifted = 1;
        $scope = 'n';

        // get most severe ban for this user_id and ip
        $user_id = $ban->user_id;
        $ip = $ban->ip;
        $banned = check_if_banned($pdo, $user_id, $ip, 'b', false);
        if ($banned !== false) {
            $ban_id = $banned->ban_id;
            $lifted = 0;
            $scope = $banned->scope;
            $ban_time = $banned->time;
            $expire_time = $banned->expire_time;
        }

        // format the ban to prepare for sending to the servers
        $most_severe = new stdClass();
        $most_severe->ban_id = $ban_id;
        $most_severe->scope = $scope;
        $most_severe->user_id = $user_id;
        $most_severe->ip = $ip;
        $most_severe->time = $ban_time;
        $most_severe->expire_time = $expire_time;
        $most_severe->lifted = $lifted;
        $bans_to_send[] = $most_severe;
    }

    // gather data to send to active servers
    $send = new stdClass();
    $send->artifact = artifact_location_select($pdo);
    $send->recent_pms = get_recent_pms($pdo);
    $send->recent_bans = $bans_to_send;
    $send_str = json_encode($send);

    // check servers and send the data
    $server_list = servers_select($pdo);
    $servers = poll_servers($server_list, 'update_cycle`' . $send_str);

    // process replies
    foreach ($servers as $server) {
        if ($server->result != false && $server->result != null) {
            $happy_hour = (int) $server->result->happy_hour;
            output("(ID #$server->server_id) is up.");
            save_plays($pdo, $server->result->plays);
            save_gp($pdo, $server->server_id, $server->result->gp);
            server_update_status(
                $pdo,
                $server->server_id,
                $server->result->status,
                $server->result->population,
                $happy_hour
            );
        } else {
            output("$server->server_id is down.");
            server_update_status($pdo, $server->server_id, 'down', 0, 0);
        }
    }
    output("Update cycle complete.\n");
}


function write_server_status($pdo)
{
    $servers = servers_select($pdo);
    $displays = array();
    foreach ($servers as $server) {
        $display = new stdClass();
        output("Writing status for $server->server_name (ID #$server->server_id)...");
        $display->server_id = (int) $server->server_id;
        $display->server_name = preg_replace("/[^A-Za-z0-9 ]/", '', $server->server_name);
        $display->address = $server->address;
        $display->port = (int) $server->port;
        $display->population = (int) $server->population;
        $display->status = $server->status;
        $display->uptime = (int) $server->uptime;
        $display->guild_id = (int) $server->guild_id;
        $display->tournament = (int) $server->tournament;
        $display->happy_hour = (int) ($server->happy_hour > 0);
        $display->hh_hour = $server->hh_hour !== null ? (int) $server->hh_hour : null;
        $displays[] = $display;
        output("Status written for $server->server_name (ID #$server->server_id).");
    }

    $save = new stdClass();
    $save->servers = $displays;
    $display_str = json_encode($save);

    output('Outputting server status:');
    output($display_str);

    file_put_contents(WWW_ROOT . '/files/server_status_2.txt', $display_str);

    output("Server status output successful.\n");
}


function get_recent_pms($pdo)
{
    $file = CACHE_DIR . '/last-pm.txt';

    // get the last message id that a notifacation was sent for
    $last_message_id = file_get_contents($file);
    if (!isset($last_message_id)) {
        $last_message_id = 0;
    }

    // select the messages
    output("Last Message ID: $last_message_id");
    $messages = messages_select_recent($pdo, $last_message_id);

    if (count($messages) > 0) {
        $last_message = $messages[count($messages) - 1];
        $last_message_id = $last_message->message_id;
    }

    // save the message id for next time
    file_put_contents($file, $last_message_id);

    output("Wrote last message ID to $file.\n");

    // done
    return $messages;
}


function save_plays($pdo, $plays)
{
    if (!empty($plays)) {
        foreach ($plays as $course => $plays) {
            level_increment_play_count($pdo, $course, $plays);
        }
    }
}


function save_gp($pdo, $server_id, $gp_array)
{
    if (!empty($gp_array)) {
        foreach ($gp_array as $user_id => $gp) {
            $user = user_select($pdo, $user_id);
            $guild_id = $user->guild;
            if ($guild_id > 0 && $server_id == $user->server_id) {
                gp_increment($pdo, $user_id, $guild_id, $gp);
                guild_increment_gp($pdo, $guild_id, $gp);
            }
        }
    }
}


function update_artifact($pdo)
{
    $lotw_file = WWW_ROOT . '/files/level_of_the_week.json';

    // collect data
    $artifacts = artifact_locations_select($pdo, true);
    if (!isset($artifacts[0])) {
        return;
    }

    // read current artifact file
    $arti_txt = file_get_contents($lotw_file);
    if ($arti_txt) {
        $arti_obj = json_decode($arti_txt);
        $cur_txt = @$arti_obj->current;
        $sched_txt = @$arti_obj->scheduled;
    }
    
    // only continue if one of these conditions are met
    // note: this won't update in the case of a usergroup change, but that can be done manually since it's rare
    if (!$arti_text) {
        output('creating an initial lotw file');
    } elseif (isset($artifacts[1]) && $artifacts[1]->set_time <= time()) { // sched arti set_time met/exceeded
        output('Placing new artifact via scheduled update.');
        artifact_location_delete_old($pdo);
        $artifacts = [$artifacts[1]];
    } elseif (isset($artifacts[1]) && @$sched_txt->updated_time != $artifacts[1]->updated_time) { // update to sched
        output('Updating scheduled artifact placement.');
    } elseif ($artifacts[0]->updated_time > $cur_txt->updated_time) { // instant update to current arti
        output('Placing new artifact (or updating current one) via instant update.');
    } elseif ($artifacts[0]->first_finder > 0 && empty($cur_txt->first_finder->group)) { // finder updates
        output('Updating the first finder.');
    } elseif ($artifacts[0]->bubbles_winner > 0 && empty($cur_txt->bubbles_winner->group)) { // update bubbles winner
        output('Updating the bubbles winner.');
    } else {
        return;
    }

    // display data
    $arti_type = 'current';
    $r = new stdClass();
    foreach ($artifacts as $artifact) {
        $level_id = (int) $artifact->level_id;
        $level = level_select($pdo, $level_id);
        $author = user_select($pdo, (int) $level->user_id);

        $arti = new stdClass();
        $arti->level = new stdClass();
        $arti->level->id = $level_id;
        $arti->level->title = $level->title;
        $arti->level->author = new stdClass();
        $arti->level->author->name = $author->name;
        $arti->level->author->group = get_group_info($author)->str;
        $arti->set_time = (int) $artifact->set_time;
        $arti->updated_time = (int) $artifact->updated_time;

        // show first finder and bubbles winner
        if ($arti_type === 'current') {
            // get the first finder's info
            $finder = new stdClass();
            $finder->name = '';
            $finder->group = 0;
            if ($artifact->first_finder > 0) {
                $found = user_select($pdo, (int) $artifact->first_finder);
                $finder->name = $found->name;
                $finder->group = get_group_info($found)->str;
                $arti->first_finder = $finder;

                // get the bubbles winner's info
                $bubbles = new stdClass();
                $bubbles->name = '';
                $bubbles->group = 0;
                if ($artifact->first_finder == $artifact->bubbles_winner) {
                    $bubbles = $finder;
                } elseif ($artifact->bubbles_winner > 0) {
                    $bub = user_select($pdo, (int) $artifact->bubbles_winner);
                    $bubbles->name = $bub->name;
                    $bubbles->group = get_group_info($bub)->str;
                }
                $arti->bubbles_winner = $bubbles;
            }
        }

        $r->$arti_type = $arti;
        $arti_type = 'scheduled'; // for next loop
    }

    // write to the file system
    file_put_contents($lotw_file, json_encode($r, JSON_UNESCAPED_UNICODE));
}


function set_campaign($pdo)
{
    output("Starting campaign update process...");

    // get campaign data
    $send = new stdClass();
    $send->campaign = campaign_select($pdo);
    $send = json_encode($send);

    // send update function to the servers
    $servers = servers_select($pdo);
    foreach ($servers as $server) {
        output("Updating campaign on $server->server_name (ID: #$server->server_id)...");
        try {
            $reply = talk_to_server($server->address, $server->port, $server->salt, "set_campaign`$send", true, false);
            output("Reply: $reply");
            output("$server->server_name (ID #$server->server_id) campaign update successful.");
        } catch (Exception $e) {
            output($e->getMessage());
        }
    }

    // tell the command line
    output('Campaign update complete.');
}


function ensure_awards($pdo)
{
    // select all records, they get cleared out weekly or somesuch
    $awards = part_awards_select_list($pdo);

    // give users their awards
    if (!empty($awards)) {
        foreach ($awards as $row) {
            $part = (int) $row->part === 0 ? '*' : $row->part;
            $type = $row->type;
            $user_id = (int) $row->user_id;
            try {
                if ($type !== 'exp') {
                    award_part($pdo, $user_id, $type, $part);
                } else {
                    award_exp($pdo, $user_id, $part, true);
                }
                echo "user_id: $user_id, type: $type, part: $part \n";
            } catch (Exception $e) {
                echo "Error: $e \n";
            }
        }
    }

    // delete older records
    part_awards_delete_old($pdo);
}


function check_expired_rank_token_rentals($pdo)
{
    $expired = rank_token_rentals_select_expired($pdo);
    if (empty($expired)) {
        return;
    }
    rank_token_rentals_delete_old($pdo);

    $processed_guilds = [];
    foreach ($expired as $rental) {
        if (in_array($rental->guild_id, $processed_guilds)) {
            continue;
        }
        array_push($processed_guilds, $rental->guild_id);

        $processed_buyer = false;
        $guild_members = users_select_rank_tokens_and_rentals_by_guild($pdo, $rental->guild_id);
        foreach ($guild_members as $user) {
            if (!isset($user->available_tokens)) {
                continue;
            }
            $max_tokens = $user->available_tokens + $user->active_rentals;
            rank_token_update($pdo, $user->user_id, min($user->used_tokens, $max_tokens));
            $processed_buyer = !$processed_buyer ? $user->user_id == $rental->user_id : true;
        }
        if (!$processed_buyer) {
            $buyer = rank_token_select($pdo, $rental->user_id);
            rank_token_update($pdo, $buyer->user_id, min($buyer->used_tokens, $buyer->available_tokens));
        }
    }
}


function servers_restart_all($pdo)
{
    // tell the command line
    $time = date('r');
    output("Mandatory server reboot CRON starting at $time...");

    // grab active servers
    $servers = servers_select($pdo);

    // shut down all active servers
    foreach ($servers as $server) {
        output("Shutting down $server->server_name (ID: #$server->server_id)...");
        try {
            $reply = talk_to_server($server->address, $server->port, $server->salt, 'shut_down`', true);
            output("Reply: $reply");
            output("$server->server_name (ID #$server->server_id) shut down successful.");
        } catch (Exception $e) {
            output($e->getMessage());
        }
    }

    // tell the command line
    output('Mandatory reboot finished.');
}


function delete_old_accounts($pdo)
{
    set_time_limit(0);
    $start_time = time();

    // get data
    $users = users_select_old($pdo);
    $nopr2 = users_select_no_pr2($pdo);

    // tell the world
    $num_users = number_format(count($users) + count($nopr2));
    $lang = $num_users === '1' ? 'account meets' : 'accounts meet';
    output("$num_users $lang the criteria for deletion.");

    // count
    $spared = 0;
    $deleted = 0;

    // delete or spare users with pr2 data
    output('Now processing users with pr2 data...');
    foreach ($users as $row) {
        $user_id = (int) $row->user_id;
        $rank = (int) $row->rank;
        $play_count = user_select_level_plays($pdo, $user_id, true);

        $str = "$user_id has $play_count level plays and is rank $rank.";
        if ($play_count > 100 || $rank > 15) {
            output("$str Spared!");
            $spared++;
        } else {
            output("$str DELETING...");
            user_delete($pdo, $user_id);
            output("$user_id was successfully deleted.");
            $deleted++;
        }
    }
    output('Processing for users with pr2 data finished.');

    // delete or spare users without pr2 data
    output('Now processing users without pr2 data...');
    foreach ($nopr2 as $row) {
        $user_id = (int) $row->user_id;
        output("$user_id has no pr2 data. DELETING...");
        user_delete($pdo, $user_id);
        output("$user_id was successfully deleted.");
        $deleted++;
    }
    output('Processing for users without pr2 data finished.');

    // tell the world
    $t_elapsed = time() - $start_time;
    $time = format_duration($t_elapsed);
    output(
        "Old account deletion completed. Stats:\n".
        "Spared: $spared / $num_users\n".
        "Deleted: $deleted / $num_users\n".
        "Time: $time ($t_elapsed seconds)"
    );
}


// -- FAH -- \\


// fah update (combine all functions)
function fah_update($pdo)
{
    // create a list of existing users and their prizes
    $prize_array = array();
    $folding_rows = folding_select_list($pdo);
    foreach ($folding_rows as $row) {
        $prize_array[strtolower($row->name)] = $row;
    }

    // get fah user stats
    $stats = fah_fetch_stats();
    if ($stats === false) {
        throw new Exception('Could not fetch FAH stats.');
    }

    // award prizes
    foreach ($stats->users as $user) {
        fah_award_prizes($pdo, $user->name, $user->points, $prize_array);
    }
}


// get stats from fah server
function fah_fetch_stats()
{
    // tell the world
    output('Fetching stats from FAH server...');

    // load the team page
    output('Loading the team page...');
    $contents = file_get_contents('https://apps.foldingathome.org/teamstats/team143016.html');
    output('Team page loaded! Checking for an active update...');
    $contents = str_replace('_', ' ', $contents); //replace "_" with " "
    $contents = trim($contents);

    // give up if fah is doing a stat update
    if ($contents == false ||
        $contents == '' ||
        strpos($contents, 'Stats update in progress') !== false ||
        strpos($contents, 'The database server is currently serving too many connections.') !== false
    ) {
        output('FATAL ERROR: FAH is currently updating. We\'ll try again later.');
        return false;
    }

    // tell the world
    output('The stats aren\'t updating. Parsing list...');

    // parse user stats
    $users_start_index = strpos($contents, '<table class="members">');
    $user_strs = substr($contents, $users_start_index);
    $user_array = explode('<tr>', $user_strs);
    $user_array = array_splice($user_array, 2);
    $users_json = array();

    foreach ($user_array as $user_str) {
        $array = explode('<td>', $user_str);

        $team_rank = explode("<", $array[2])[0];
        $name = explode("<", $array[3])[0];
        $points = explode("<", $array[4])[0];
        $work_units = explode("<", $array[5])[0];

        if (strlen($name) > 50) {
            $name = substr($name, 0, 50);
        }

        $user = new stdClass();
        $user->name = $name;
        $user->points = $points;
        $user->work_units = $work_units;
        $user->team_rank = $team_rank;

        $users_json[] = $user;
    }

    // throw all of the data into a readable object
    output('List parsed! Starting to award prizes...');
    $r = new stdClass();
    $r->users = $users_json;
    return $r;
}


// validate, determine, and award fah prizes
function fah_award_prizes($pdo, $name, $score, $prize_array)
{
    $safe_name = htmlspecialchars($name, ENT_QUOTES);
    $lower_name = strtolower($name);

    try {
        if (isset($prize_array[$lower_name])) {
            $user_data = $prize_array[$lower_name];
        } else {
            $user = user_select_by_name($pdo, $name, true);
            if ($user === false) {
                throw new Exception("Could not find a user with the name $safe_name.");
            }

            // make fah entry for this user
            folding_insert($pdo, $user->user_id);
            $user_data = folding_select_by_user_id($pdo, $user->user_id);
        }

        // make variables from row data
        $user_id = (int) $user_data->user_id;
        $status = $user_data->status;
        $hat_array = explode(',', $user_data->hat_array);
        $epic_hats = explode(',', $user_data->epic_hats);
        $available_tokens = (int) $user_data->available_tokens;

        // don't continue if offline
        if ($status != 'offline') {
            throw new Exception("$safe_name is \"$status\". We'll try again later.");
        }

        // --- ensure awards and give new ones --- \\

        // define columns
        $columns = array(
            'r1' => array('token' => 1, 'min_score' => 1),
            'r2' => array('token' => 2, 'min_score' => 500),
            'r3' => array('token' => 3, 'min_score' => 1000),
            'crown_hat' => array('hat' => 'crown', 'min_score' => 5000),
            'cowboy_hat' => array('hat' => 'cowboy', 'min_score' => 100000),
            'epic_crown' => array('ehat' => 'crown', 'min_score' => 500000),
            'epic_cowboy' => array('ehat', 'cowboy', 'min_score' => 5000000),
            'r4' => array('token' => 4, 'min_score' => 1000000),
            'r5' => array('token' => 5, 'min_score' => 10000000),
            'r6' => array('token' => 6, 'min_score' => 25000000),
            'r7' => array('token' => 7, 'min_score' => 50000000),
            'r8' => array('token' => 8, 'min_score' => 100000000)
        );

        // get number of folded tokens/hats
        $token_awards = array();
        $award_crown = $award_ecrown = $award_cb = $award_ecb = false;
        foreach ($columns as $column => $data) {
            // sanity check: is the score less than the min_score?
            if ($data['min_score'] > $score) {
                continue;
            }
            // determine the column to check
            if (strpos($column, 'r') === 0) {
                array_push($token_awards, $data);
            } elseif ($column === 'crown_hat') {
                $award_crown = true;
            } elseif ($column === 'epic_crown') {
                $award_ecrown = true;
            } elseif ($column === 'cowboy_hat') {
                $award_cb = true;
            } elseif ($column === 'epic_cowboy') {
                $award_ecb = true;
            }
        }

        // award tokens
        if ($available_tokens !== count($token_awards)) {
            foreach ($token_awards as $column) {
                fah_award_token($pdo, $user_id, $name, $score, $column, $available_tokens);
            }
        }

        // award crown hat
        $has_crown = in_array('6', $hat_array);
        if (($award_crown === true || $score > 5000) && $has_crown === false) {
            fah_award_part($pdo, $user_id, $name, $score, 'hat', 6);
        }

        // award cowboy hat
        $has_cb = in_array('5', $hat_array);
        if (($award_cb === true || $score > 100000) && $has_cb === false) {
            fah_award_part($pdo, $user_id, $name, $score, 'hat', 5);
        }

        // award epic crown hat
        $has_ecrown = in_array('6', $epic_hats);
        if (($award_ecrown === true || $score > 500000) && $has_ecrown === false) {
            fah_award_part($pdo, $user_id, $name, $score, 'ehat', 6);
        }

        // award epic cowboy hat
        $has_ecb = in_array('5', $epic_hats);
        if (($award_ecb === true || $score > 5000000) && $has_ecb === false) {
            fah_award_part($pdo, $user_id, $name, $score, 'ehat', 5);
        }

        // tell the world
        output("Finished $safe_name.");
    } catch (Exception $e) {
        output($e->getMessage());
    }
}


// award tokens
function fah_award_token($pdo, $user_id, $name, $score, $column, $available_tokens)
{
    $safe_name = htmlspecialchars($name, ENT_QUOTES);
    $token_num = $column['token'];
    $column = "r$token_num";

    try {
        // verify that the correct amount of points has been folded for this prize
        if (($column === 'r1' && $score < 1)
            || ($column === 'r2' && $score < 500)
            || ($column === 'r3' && $score < 1000)
            || ($column === 'r4' && $score < 1000000)
            || ($column === 'r5' && $score < 10000000)
            || ($column === 'r6' && $score < 25000000)
            || ($column === 'r7' && $score < 50000000)
            || ($column === 'r8' && $score < 100000000)
        ) {
            throw new Exception("$safe_name ($user_id): Insufficient score ($score) for that folding prize ($column).");
        }

        if ($token_num <= $available_tokens) {
            throw new Exception("$safe_name ($user_id): This user already has $column. Moving on...");
        }

        // do it
        output("Awarding $column to $safe_name...");
        rank_token_upsert($pdo, $user_id, $token_num);

        // finalize it (send message, mark as awarded in folding_at_home)
        fah_finalize_award($pdo, $user_id, $name, $column);
    } catch (Exception $e) {
        output(htmlspecialchars($e->getMessage(), ENT_QUOTES));
    }
}


// award parts
function fah_award_part($pdo, $user_id, $name, $score, $type, $id)
{
    $allowed_prizes = ['crown_hat', 'epic_crown', 'cowboy_hat', 'epic_cowboy'];
    $safe_name = htmlspecialchars($name, ENT_QUOTES);

    try {
        // define code
        $epic = strpos($type, 'e') === 0;
        $base_type = $epic ? substr($type, 1) : $type;
        if ($base_type === 'hat') {
            if ($id === 6) {
                $column = $epic ? 'epic_crown' : 'crown_hat';
            } elseif ($id === 5) {
                $column = $epic ? 'epic_cowboy' : 'cowboy_hat';
            }
        }

        // sanity check: is the prize an actual prize?
        if (!in_array($column, $allowed_prizes)) {
            throw new Exception("$safe_name ($user_id): Invalid part prize ($column).");
        }

        // sanity check: has the correct amount of points been folded for this prize?
        if (($column === 'crown_hat' && $score < 5000)
            || ($column === 'cowboy_hat' && $score < 100000)
            || ($column === 'epic_crown' && $score < 500000)
            || ($column === 'epic_cowboy' && $score < 5000000)
        ) {
            throw new Exception("$safe_name ($user_id): Insufficient score ($score) for that folding prize ($column).");
        }

        // do it
        output("Awarding $column to $safe_name...");
        award_part($pdo, $user_id, $type, $id);

        // finalize it (send message, mark as awarded in folding_at_home)
        fah_finalize_award($pdo, $user_id, $name, $column);
    } catch (Exception $e) {
        output($e->getMessage());
    }
}


// tell the world and remember
function fah_finalize_award($pdo, $user_id, $name, $prize_code)
{
    $rt_desc = 'a rank token';
    $prizes = array(
        'r1' => array($rt_desc, 1),
        'r2' => array($rt_desc, 500),
        'r3' => array($rt_desc, 1000),
        'r4' => array($rt_desc, 1000000),
        'r5' => array($rt_desc, 10000000),
        'r6' => array($rt_desc, 25000000),
        'r7' => array($rt_desc, 50000000),
        'r8' => array($rt_desc, 100000000),
        'crown_hat' => array('the Crown Hat', 5000),
        'cowboy_hat' => array('the Cowboy Hat', 100000),
        'epic_crown' => array('the epic upgrade for the Crown Hat', 500000),
        'epic_cowboy' => array('the epic upgrade for the Cowboy Hat', 5000000)
    );

    // compose a PM
    $safe_name = htmlspecialchars($name, ENT_QUOTES);
    $prize_str = $prizes[$prize_code][0];
    $min_score = number_format($prizes[$prize_code][1]) . ' point' . ($prizes[$prize_code][1] > 1 ? 's' : '');
    $message = "Dear $safe_name,\n\n"
        ."Congratulations on earning $min_score for Team Jiggmin! "
        ."As a special thank you, I've added $prize_str to your account!!\n\n"
        ."Thanks for helping us take over the world (or cure cancer)!\n\n"
        ."- Jiggmin";

    // send the folder the composed message
    message_insert($pdo, $user_id, 1, $message, '0');

    // remember that this prize has been given
    folding_update($pdo, $user_id, $prize_code);

    // output
    output("Finished awarding $prize_code to $name ($user_id).");
}
