<?php


// -- CRON -- \\


function run_update_cycle($pdo)
{
    output('Running update cycle...');

    // gather data to send to active servers
    $send = new stdClass();
    $send->artifact = artifact_location_select($pdo);
    $send->recent_pms = get_recent_pms($pdo);
    $send->recent_bans = bans_select_recent($pdo);
    $send_str = json_encode($send);

    // send the data
    $server_list = servers_select($pdo);
    $servers = poll_servers($server_list, 'update_cycle`' . $send_str);

    // process replies
    foreach ($servers as $server) {
        if ($server->result != false && $server->result != null) {
            $happy_hour = (int)$server->result->happy_hour;
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
    output('Update cycle complete.');
}


function write_server_status($pdo)
{
    $servers = servers_select($pdo);
    $displays = array();
    foreach ($servers as $server) {
        $display = new stdClass();
        output("Writing status for $server->server_name (ID #$server->server_id)...");
        $display->server_id = $server->server_id;
        $display->server_name = preg_replace("/[^A-Za-z0-9 ]/", '', $server->server_name);
        $display->address = $server->address;
        $display->port = $server->port;
        $display->population = $server->population;
        $display->status = $server->status;
        $display->guild_id = $server->guild_id;
        $display->tournament = $server->tournament;
        $display->happy_hour = $server->happy_hour;
        $displays[] = $display;
        output("Status written for $server->server_name (ID #$server->server_id).");
    }

    $save = new stdClass();
    $save->servers = $displays;
    $display_str = json_encode($save);

    output('Outputting server status:');
    output($display_str);

    file_put_contents(WWW_ROOT . '/files/server_status_2.txt', $display_str);

    output('Server status output successful.');
}


function get_recent_pms($pdo)
{
    $file = COMMON_DIR . '/cron/last-pm.txt';

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

    output("Wrote last message ID to $file.");

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
    // collect data
    $artifact = artifact_location_select($pdo);
    $level_id = (int) $artifact->level_id;
    $updated_time = strtotime($artifact->updated_time);
    $first_finder = (int) $artifact->first_finder;
    $bubbles_winner = (int) $artifact->bubbles_winner;

    $level = level_select($pdo, $level_id);
    $title = $level->title;
    $user_id = (int) $level->user_id;

    $user = user_select($pdo, $user_id);
    $user_name = $user->name;

    if ($first_finder !== 0) {
        $finder = user_select($pdo, $first_finder);
        $finder_name = $finder->name;
        $finder_group = (int) $finder->power;
    } else {
        $finder_name = '';
        $finder_group = 0;
    }

    if ($bubbles_winner !== 0) {
        $bubbles = user_select($pdo, $bubbles_winner);
        $bubbles_name = $bubbles->name;
        $bubbles_group = (int) $bubbles->power;
    } else {
        $bubbles_name = '';
        $bubbles_group = 0;
    }

    // form the base string we'll be creating
    $str = "$title by $user_name";
    $len = strlen($str);

    // figure out how much of the string to reveal
    $elapsed = time() - $updated_time;
    $perc = $elapsed / 259200; // 3 days
    $perc = $perc > 1 ? 1 : $perc; // full
    $hide_perc = 1 - $perc;
    $hide_characters = round($len * $hide_perc);

    // generate random
    \pr2\http\PseudoRandom::seed(112);

    // replace a percentage of characters with underscores
    $arr = str_split($str);
    $loops = 0;
    while ($hide_characters > 0) {
        $index = \pr2\http\PseudoRandom::num(0, $len - 1);
        while ($arr[$index] === '_') {
            $index++;
            $index = $index >= $len ? 0 : $index;

            $loops++;
            if ($loops > 100) {
                output('Infinite loop triggered, breaking...');
                break;
            }
        }
        $arr[$index] = '_';
        $hide_characters--;
    }


    // tell it to the world
    $r = new stdClass();
    $r->hint = join('', $arr);
    $r->finder_name = $finder_name;
    $r->finder_group = $finder_group;
    $r->bubbles_name = $bubbles_name;
    $r->bubbles_group = $bubbles_group;
    $r->updated_time = $updated_time;
    $r_str = json_encode($r);

    file_put_contents(WWW_ROOT . '/files/artifact_hint.txt', $r_str);
    output($r->hint);
}


function failover_servers($pdo)
{
    // list servers
    $servers = servers_select($pdo);
    $addresses = array('45.76.24.255'); // todo: this should be in the db

    // restart if down
    foreach ($servers as $server) {
        if ($server->status == 'down') {
            $fallback_address = $addresses[array_rand($addresses)];
            server_update_address($pdo, $server->server_id, $fallback_address);
        }
    }

    // tell the world
    output('The correct address of all active servers has been ensured.');
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
    foreach ($awards as $row) {
        $part = (int) $row->part === 0 ? '*' : $row->part;
        $type = $row->type;
        $user_id = (int) $row->user_id;
        try {
            award_part($pdo, $user_id, $type, $part, false);
            echo "user_id: $user_id, type: $type, part: $part \n";
        } catch (Exception $e) {
            echo "Error: $e \n";
        }
    }

    // delete older records
    part_awards_delete_old($pdo);
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
    $contents = file_get_contents('http://fah-web.stanford.edu/teamstats/team143016.html');
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
            $row = $prize_array[$lower_name];
            $user_id = $row->user_id;
            $status = $row->status;
        } else {
            $user = user_select_by_name($pdo, $name, true);
            if ($user === false) {
                throw new Exception("Could not find a user with the name $safe_name.");
            }

            // make some variables
            $user_id = $user->user_id;
            $status = $user->status;

            folding_insert($pdo, $user_id);
            $row = folding_select_by_user_id($pdo, $user_id);
        }

        if ($status != 'offline') {
            throw new Exception("$safe_name is \"$status\". We'll try again later.");
        }

        // --- ensure awards and give new ones --- \\

        // get information from pr2, rank_tokens, and folding_at_home
        $hat_array = explode(',', pr2_select($pdo, $user_id)->hat_array);
        $rank_token_row = rank_token_select($pdo, $user_id);

        // avoid getting object of false
        if ($rank_token_row !== false) {
            $available_tokens = (int) $rank_token_row->available_tokens;
        } else {
            $available_tokens = 0;
        }

        // define columns
        $columns = array(
            'r1' => array('token' => 1, 'min_score' => 1),
            'r2' => array('token' => 2, 'min_score' => 500),
            'r3' => array('token' => 3, 'min_score' => 1000),
            'crown_hat' => array('hat' => 'crown', 'min_score' => 5000),
            'cowboy_hat' => array('hat' => 'cowboy', 'min_score' => 100000),
            'r4' => array('token' => 4, 'min_score' => 1000000),
            'r5' => array('token' => 5, 'min_score' => 10000000)
        );

        // get number of folded tokens/hats
        $token_awards = array();
        $award_crown = false;
        $award_cb = false;
        foreach ($columns as $column => $data) {
            // sanity check: is the score less than the min_score?
            if ($data['min_score'] > $score) {
                continue;
            }
            // determine the column to check
            if (strpos($column, 'r') === 0) {
                array_push($token_awards, $data);
            } elseif ($column == 'crown_hat') {
                $award_crown = true;
            } elseif ($column == 'cowboy_hat') {
                $award_cb = true;
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
            fah_award_hat($pdo, $user_id, $name, $score, 'crown');
        }

        // award cowboy hat
        $has_cb = in_array('5', $hat_array);
        if (($award_cb === true || $score > 100000) && $has_cb === false) {
            fah_award_hat($pdo, $user_id, $name, $score, 'cowboy');
        }

        // tell the world
        output("Finished $safe_name.");
    } catch (Exception $e) {
        $error = $e->getMessage();
        $safe_error = htmlspecialchars($error, ENT_QUOTES);
        output($safe_error);
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
        if (($column == 'r1' && $score < 1)
            || ($column == 'r2' && $score < 500)
            || ($column == 'r3' && $score < 1000)
            || ($column == 'r4' && $score < 1000000)
            || ($column == 'r5' && $score < 10000000)
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


// award hats
function fah_award_hat($pdo, $user_id, $name, $score, $hat)
{
    $safe_name = htmlspecialchars($name, ENT_QUOTES);
    $code = $hat . '_hat';

    try {
        // define hat id
        if ($hat == 'crown') {
            $hat_id = 6;
        } elseif ($hat == 'cowboy') {
            $hat_id = 5;
        } // sanity check: is the prize an actual prize?
        else {
            throw new Exception("$safe_name ($user_id): Invalid hat prize ($hat).");
        }

        // sanity check: has the correct amount of points been folded for this prize?
        if (($hat == 'crown' && $score < 5000) || ($hat == 'cowboy' && $score < 100000)) {
            throw new Exception("$safe_name ($user_id): Insufficient score ($score) for that folding prize ($code).");
        }

        // do it
        output("Awarding $code to $safe_name...");
        award_part($pdo, $user_id, 'hat', $hat_id);

        // finalize it (send message, mark as awarded in folding_at_home)
        fah_finalize_award($pdo, $user_id, $name, $code);
    } catch (Exception $e) {
        output($e->getMessage());
    }
}


// tell the world and remember
function fah_finalize_award($pdo, $user_id, $name, $prize_code)
{
    $rt_desc = 'a rank token';
    $prizes = array(
        'r1' => array($rt_desc, '1 point'),
        'r2' => array($rt_desc, '500 points'),
        'r3' => array($rt_desc, '1,000 points'),
        'r4' => array($rt_desc, '1,000,000 points'),
        'r5' => array($rt_desc, '10,000,000 points'),
        'crown_hat' => array('the Crown Hat', '5,000 points'),
        'cowboy_hat' => array('the Cowboy Hat', '100,000 points')
    );

    // compose a PM
    $safe_name = htmlspecialchars($name, ENT_QUOTES);
    $prize_str = $prizes[$prize_code][0];
    $min_score = $prizes[$prize_code][1];
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
