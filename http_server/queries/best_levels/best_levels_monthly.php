<?php

require_once 'best_levels_truncate.php';
require_once 'best_levels_populate.php';

function best_levels_monthly($pdo)
{
    best_levels_truncate($pdo);
    best_levels_populate($pdo);
}
