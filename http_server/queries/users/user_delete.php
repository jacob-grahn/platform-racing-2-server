<?php

function user_delete($pdo, $user_id)
{
	$stmt = $pdo->prepare('
        DELETE FROM artifacts_found
        WHERE user_id = :user_id;

        DELETE FROM bans
        WHERE banned_user_id = :user_id;

        DELETE FROM bounce
        WHERE user_id = :user_id;

        DELETE FROM flagged_messages
        WHERE from_user_id = :user_id
        OR to_user_id = :user_id;

        DELETE FROM folding_at_home
        WHERE user_id = :user_id;

        DELETE FROM friends
        WHERE user_id = :user_id
        OR friend_id = :user_id;

        DELETE FROM ignored
        WHERE user_id = :user_id
        OR ignore_id = :user_id;

        DELETE FROM messages
        WHERE to_user_id = :user_id
        OR from_user_id = :user_id;

        DELETE FROM messages_reported
        WHERE to_user_id = :user_id
        OR from_user_id = :user_id;

        DELETE FROM mod_power
        WHERE user_id = :user_id;

        DELETE FROM pr2
        WHERE user_id = :user_id;

        DELETE FROM pr2_levels
        WHERE user_id = :user_id;

        DELETE FROM pr2_ratings
        WHERE user_id = :user_id;

        DELETE FROM users
        WHERE user_id = :user_id;

        DELETE FROM part_awards
        WHERE user_id = :user_id;

        DELETE FROM rank_tokens
        WHERE user_id = :user_id;

        DELETE FROM epic_upgrades
        WHERE user_id = :user_id;

        DELETE FROM gp
        WHERE user_id = :user_id;

        DELETE FROM guild_invitations
        WHERE user_id = :user_id;

        DELETE FROM part_awards
        WHERE user_id = :user_id;

        DELETE FROM lux_today
        WHERE user_id = :user_id;

        DELETE FROM lux_total
        WHERE user_id = :user_id;

        DELETE FROM tokens
        WHERE user_id = :user_id;

        DELETE FROM level_backups
        WHERE user_id = :user_id;
    ');
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('error deleting user');
    }

    return $result;
}
