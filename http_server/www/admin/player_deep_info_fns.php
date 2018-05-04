<?php

function output_objects($objs, $is_logins = false, $user = null)
{
    if ($objs !== false) {
        $count = 0;
        foreach ($objs as $obj) {
            output_object($obj, ', ');
            $count++;
        }
        if ($is_logins === true && $count > 0) {
            $url_name = urlencode($user->name);
            echo "<a href='player_deep_logins.php?name=$url_name'>more logins</a><br>";
        }
    }
}

function output_object($obj, $sep = '<br/>')
{
    if ($obj !== false) {
        foreach ($obj as $var => $val) {
            if ($var == 'email') {
                $safe_email = htmlspecialchars($val);
                $url_email = urlencode($val);
                $val = "<a href='search_by_email.php?email=$url_email'>$safe_email</a>";
                echo "$var: $val $sep";
            }
            if ($var == 'guild') {
                $val = (int) $val;
                if ($val != 0) {
                    $val = "<a href='guild_deep_info.php?guild_id=$val'>$val</a>";
                } else {
                    $val = 'none';
                }
                echo "$var: $val $sep";
            }
            if ($var == 'time' || $var == 'register_time') {
                $val = date('M j, Y g:i A', $val);
            }
            if ($var != 'user_id' && $var != 'email' && $var != 'guild') {
                echo "$var: ".htmlspecialchars($val)."$sep";
            }
        }
        if ($sep != '<br/>') {
            echo '<br>';
        }
    }
}

function output_object_keys($obj, $sep = ', ')
{
    if ($obj !== false && $obj !== null) {
        foreach ($obj as $var => $val) {
            if ($val == 1 && $var != 'user_id') {
                echo $var.$sep;
            }
        }
        echo '<br>';
    }
}
