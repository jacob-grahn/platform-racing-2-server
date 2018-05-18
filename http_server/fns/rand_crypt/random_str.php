<?php

function random_str($len)
{
    return substr(bin2hex(random_bytes($len)), 0, $len);
}
