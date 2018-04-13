<?php

// this will echo the search box when called
function output_search($ip = '', $incl_br = true)
{
    echo "<form name='input' action='' method='get'>";
    echo "IP: <input type='text' name='ip' value='$ip'>&nbsp;";
    echo "<input type='submit' value='Search'></form>";
    if ($incl_br) {
        echo "<br>";
    }
}
