<?php


// this will echo the search box when called
function output_search($query = '', $incl_br = true)
{
    echo "<form name='input' action='' method='get'>";
    echo "Search: <input type='text' name='query' value='$query'>&nbsp;";
    echo "<input type='submit' value='Search'></form>";
    if ($incl_br) {
        echo "<br><br>";
    }
}
