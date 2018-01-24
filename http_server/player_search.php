<?php

require_once('../fns/output_fns.php');

error_reporting(0);

output_header( 'Player Search' );

$param = http_build_query(array(
    'name' => $_GET['name'],
    'rand' => rand(),
));

$server = file_get_contents("https://pr2hub.com/get_player_info_2.php?".$param);

$json = json_decode($server);

$errorhtml = "<br>";
   
    echo "<center>";
    
    echo "<img src='http://pr2hub.com/img/player_search.png' />
<br><br>
<form method='get'>
<label for='name' class='full'>Username: </label>
<input type='text' name='name' class='primary full textbox' id='name' value='' tabindex='1' required='true'>        
<input type='submit' value='Search' class='button' accesskey='s' tabindex='2'>
</form>";

if(isset($_GET['name'])) {

if(isset($json->userId)) {

    echo "<br>";
    
    echo "<center>-- <u><font color=\"";
    if ($json->group == "0") {
        echo "#7e7f7f\">";
    }
    elseif ($json->group == "1") {
        echo "#047b7b\">";
    }
    elseif ($json->group == "2") {
        echo "#1c369f\">";
    }
    elseif ($json->group == "3") {
        echo "#870a6f\">";
    }
    else {
        die("Hmm, something went wrong with the user name color. Try refreshing the page!");
    }
    echo $json->name . "</font></u> --";
    
    echo "<br><i>" . $json->status . "</i></center>";

    echo "<br>" . "Group: ";
    
    if ($json->group == "0") {
        echo "Guest";
    }
    elseif ($json->group == "1") {
        echo "Member";
    }
    elseif ($json->group == "2") {
        echo "Moderator";
    }
    elseif ($json->group == "3") {
        echo "Admin";
    }
    else {
        die("Hmm, something went wrong with the group. Try refreshing the page!");
    }
    echo "<br>Guild: ";

    if ($json->guildId !== "0") {
        echo "<a href=\"http://pr2hub.com/guild_search.php?name=" . $json->guildName . "\" target=\"_blank" . "\">";
        echo $json->guildName;
        echo "</a>";
    }
    elseif ($json->guildId == "0") {
        echo "none";
    }
    else {
        echo $json->guildName;
    }

    echo "<br><br>" . "Rank: " . $json->rank;

    echo "<br>" . "Hats: " . $json->hats;

    echo "<br><br>" . "Joined: ";
    if ($json->registerDate == "1/Jan/1970") {
        echo "Age of Heroes";
    }
    else {
        echo $json->registerDate;
    }

        echo "<br>" . "Last Login: " . $json->loginDate;

    }
    }

    if (isset($json->error)) {
        echo $errorhtml;
        echo "Error: ";
        echo $json->error;
    }

        echo "</center>";

output_footer();        

?>
