<?php

// this will echo the search box when called
function output_search($email = '', $incl_br = true)
{
    echo "<form name='input' action='' method='get'>";
    echo "Email: <input type='text' name='email' value='$email'>&nbsp;";
    echo "<input type='submit' value='Search'></form>";
    if ($incl_br) {
        echo "<br><br>";
    }
}
