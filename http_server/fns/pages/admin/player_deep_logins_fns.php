<?php

// this will echo the search box when called
function output_search($name = '', $incl_br = true)
{
    echo "<form name='input' action='' method='get'>";
    echo "Username: <input type='text' name='name' value='$name'>&nbsp;";
    echo "<input type='submit' value='Search'></form>";
    if ($incl_br) {
        echo "<br><br>";
    }
}
