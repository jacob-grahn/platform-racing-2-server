<?php

// TO-DO: is this needed?
function all_optimize($pdo)
{
    $pdo->exec('OPTIMIZE TABLE artifact_location');
    $pdo->exec('OPTIMIZE TABLE bans');
    $pdo->exec('OPTIMIZE TABLE best_levels');
    $pdo->exec('OPTIMIZE TABLE bounce');
    $pdo->exec('OPTIMIZE TABLE flagged_messages');
    $pdo->exec('OPTIMIZE TABLE folding_at_home');
    $pdo->exec('OPTIMIZE TABLE friends');
    $pdo->exec('OPTIMIZE TABLE ignored');
    $pdo->exec('OPTIMIZE TABLE login_attempts');
    $pdo->exec('OPTIMIZE TABLE messages');
    $pdo->exec('OPTIMIZE TABLE messages_reported');
    $pdo->exec('OPTIMIZE TABLE pr2');
    $pdo->exec('OPTIMIZE TABLE pr2_campaign');
    $pdo->exec('OPTIMIZE TABLE pr2_levels');
    $pdo->exec('OPTIMIZE TABLE pr2_new_levels');
    $pdo->exec('OPTIMIZE TABLE pr2_ratings');
    $pdo->exec('OPTIMIZE TABLE promotion_log');
    $pdo->exec('OPTIMIZE TABLE queries');
    $pdo->exec('OPTIMIZE TABLE tokens');
    $pdo->exec('OPTIMIZE TABLE users');
    $pdo->exec('OPTIMIZE TABLE users_new');

    return true;
}
