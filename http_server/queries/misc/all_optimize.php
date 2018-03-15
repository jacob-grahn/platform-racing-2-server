<?php

function users_reset_status($pdo)
{
	$stmt = $pdo->prepare('
        OPTIMIZE TABLE artifact_location;
        OPTIMIZE TABLE bans;
        OPTIMIZE TABLE best_levels;
        OPTIMIZE TABLE bounce;
        OPTIMIZE TABLE flagged_messages;
        OPTIMIZE TABLE folding_at_home;
        OPTIMIZE TABLE friends;
        OPTIMIZE TABLE ignored;
        OPTIMIZE TABLE login_attempts;
        OPTIMIZE TABLE messages;
        OPTIMIZE TABLE messages_reported;
        OPTIMIZE TABLE pr2;
        OPTIMIZE TABLE pr2_campaign;
        OPTIMIZE TABLE pr2_levels;
        OPTIMIZE TABLE pr2_new_levels;
        OPTIMIZE TABLE pr2_ratings;
        OPTIMIZE TABLE promotion_log;
        OPTIMIZE TABLE queries;
        OPTIMIZE TABLE tokens;
        OPTIMIZE TABLE users;
        OPTIMIZE TABLE users_new;
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('error optimizing db');
    }

    return $result;
}
