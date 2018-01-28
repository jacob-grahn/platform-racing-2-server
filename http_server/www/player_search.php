<?php

require_once('../fns/output_fns.php');

error_reporting(0);

output_header( 'Player Search' );

$file = file_get_contents("https://pr2hub.com/get_player_info_2.php?name=" . $_GET['name']);

$decode = json_decode($file);

echo "<center>";

echo "<img src='/img/player_search.png' />";

echo "<br /><br />";

echo "<form method='get'>
Username: <input type='text' name='name'>
<input type='submit' value='Search'>
</form>";

if(isset($_GET['name'])) {

if(isset($decode->userId)) {

    echo "<br />";

    echo "-- <u><font color='";
    if ($decode->group == "0") {
        echo "#7E7F7F'>";
    }
    elseif ($decode->group == "1") {
        echo "#047B7B'>";
    }
    elseif ($decode->group == "2") {
        echo "#1C369F'>";
    }
    elseif ($decode->group == "3") {
        echo "#870A6F'>";
    }
    else {
        echo "#000000'>";
    }
    
echo htmlspecialchars($decode->name) . "</font></u> --";

echo "<br /><br />";

echo "Group: ";

if ($decode->group == "0") {
    echo "Guest";
}
elseif ($decode->group == "1") {
    echo "Member";
}
elseif ($decode->group == "2") {
    echo "Moderator";
}
elseif ($decode->group == "3") {
    echo "Admin";
}
else {
    echo "Unknown";
}

echo "<br /><br />";

echo "Guild: ";

if ($decode->guildId == "0") {
    echo "none";
}
else {
    echo htmlspecialchars($decode->guildName);
}

echo "<br /><br />";

echo "Rank: " . $decode->rank;

echo "<br /><br />";

echo "Hats: " . $decode->hats;

echo "<br /><br />";

echo "Joined: ";

if ($decode->registerDate == "1/Jan/1970") {
    echo "Age of Heroes";
}
else {
    echo $decode->registerDate;
}

echo "<br /><br />";

echo "Active: " . $decode->loginDate;

echo "<br /><br />";

}
}

if (isset($decode->error)) {
    echo "<br />";
    echo $decode->error;
} 

echo "</center>";

output_footer();

?>
