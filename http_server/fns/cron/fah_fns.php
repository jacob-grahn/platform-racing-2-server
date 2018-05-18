<?php

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
    $users_start_index = strpos($contents, '<BR>Team members:<BR>');
    $user_strs = substr($contents, $users_start_index);
    $user_array = explode('<TR ', $user_strs);
    $user_array = array_splice($user_array, 2);
    $users_json = array();
    
    foreach ($user_array as $user_str) {
        $array = explode('<TD>', $user_str);
    
        $team_rank = $array[2];
        $name = $array[3];
        $points = $array[4];
        $work_units = $array[5];
    
        $team_rank = substr($team_rank, 0, strlen($team_rank)-5);
        $name = substr($name, 0, strlen($name)-5);
        $points = substr($points, 0, strlen($points)-5);
        $work_units = substr($work_units, 0, strlen($work_units)-5);
    
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
    $safe_name = htmlspecialchars($name);
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
        $available_tokens = (int) rank_token_select($pdo, $user_id)->available_tokens;
        
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
        $safe_error = htmlspecialchars($error);
        output($safe_error);
    }
}

// award tokens
function fah_award_token($pdo, $user_id, $name, $score, $column, $available_tokens)
{
    $safe_name = htmlspecialchars($name);
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
        output(htmlspecialchars($e->getMessage()));
    }
}

// award hats
function fah_award_hat($pdo, $user_id, $name, $score, $hat)
{
    $safe_name = htmlspecialchars($name);
    $column = $hat . '_hat';
    
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
        if (($hat == 'crown' && $score < 5000)
            || ($hat == 'cowboy' && $score < 100000)
        ) {
            throw new Exception("$safe_name ($user_id): Insufficient score ($score) for that folding prize ($column).");
        }
        
        // do it
        output("Awarding $column to $safe_name...");
        award_part($pdo, $user_id, 'hat', $hat_id);
        
        // finalize it (send message, mark as awarded in folding_at_home)
        fah_finalize_award($pdo, $user_id, $name, $column);
    } catch (Exception $e) {
        output(htmlspecialchars($e->getMessage()));
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
    $safe_name = htmlspecialchars($name);
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
