<?php

function user_delete($pdo, $user_id)
{
    user_delete_from_artifacts_found($pdo, $user_id);
    user_delete_from_contest_winners($pdo, $user_id);
    user_delete_from_bans($pdo, $user_id);
    user_delete_from_epic_upgrades($pdo, $user_id);
    user_delete_from_favorite_levels($pdo, $user_id);
    user_delete_from_folding_at_home($pdo, $user_id);
    user_delete_from_follows($pdo, $user_id);
    user_delete_from_friends($pdo, $user_id);
    user_delete_from_gp($pdo, $user_id);
    user_delete_from_guild_invitations($pdo, $user_id);
    user_delete_from_ignored($pdo, $user_id);
    user_delete_from_level_backups($pdo, $user_id);
    user_delete_from_levels($pdo, $user_id);
    user_delete_from_messages($pdo, $user_id);
    user_delete_from_messages_reported($pdo, $user_id);
    user_delete_from_mod_power($pdo, $user_id);
    user_delete_from_part_awards($pdo, $user_id);
    user_delete_from_pr2($pdo, $user_id);
    user_delete_from_rank_tokens($pdo, $user_id);
    user_delete_from_ratings($pdo, $user_id);
    user_delete_from_recent_logins($pdo, $user_id);
    user_delete_from_tokens($pdo, $user_id);
    user_delete_from_users($pdo, $user_id);
}

function user_delete_from_artifacts_found($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM artifacts_found
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from artifacts_found.');
    }
    
    return $result;
}
        
function user_delete_from_bans($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM bans
        WHERE banned_user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from bans.');
    }
    
    return $result;
}

function user_delete_from_contest_winners($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM contest_winners
        WHERE winner_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from contest_winners.');
    }

    return $result;
}

function user_delete_from_epic_upgrades($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM epic_upgrades
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from epic_upgrades.');
    }
    
    return $result;
}

function user_delete_from_favorite_levels($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM favorite_levels
        WHERE user_id = :uid_param_1
        OR level_id IN (
            SELECT level_id FROM levels WHERE user_id = :uid_param_2
        )
    ');
    $stmt->bindValue(':uid_param_1', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':uid_param_2', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from favorite_levels.');
    }
    
    return $result;
}

function user_delete_from_folding_at_home($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM folding_at_home
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from folding_at_home.');
    }
    
    return $result;
}

function user_delete_from_follows($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM follows
        WHERE user_id = :user_id
        OR following_id = :following_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':following_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from follows.');
    }
    
    return $result;
}

function user_delete_from_friends($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM friends
        WHERE user_id = :user_id
        OR friend_id = :friend_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':friend_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from friends.');
    }
    
    return $result;
}

function user_delete_from_gp($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM gp
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from gp.');
    }
    
    return $result;
}

function user_delete_from_guild_invitations($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM guild_invitations
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from guild_invitations.');
    }
    
    return $result;
}

function user_delete_from_ignored($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM ignored
        WHERE user_id = :user_id
        OR ignore_id = :ignore_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ignore_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from ignored.');
    }
    
    return $result;
}

function user_delete_from_level_backups($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM level_backups
        WHERE user_id = :user_id
    ');
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from level_backups.');
    }
    
    return $result;
}

function user_delete_from_levels($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM levels
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from levels.');
    }
    
    return $result;
}

function user_delete_from_messages($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages
        WHERE to_user_id = :to_id
        OR from_user_id = :from_id
    ');
    $stmt->bindValue(':to_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':from_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from messages.');
    }
    
    return $result;
}

function user_delete_from_messages_reported($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages_reported
        WHERE to_user_id = :to_id
        OR from_user_id = :from_id
    ');
    $stmt->bindValue(':to_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':from_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from messages_reported.');
    }
    
    return $result;
}

function user_delete_from_mod_power($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM mod_power
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from mod_power.');
    }
    
    return $result;
}

function user_delete_from_part_awards($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM part_awards
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from part_awards.');
    }
    
    return $result;
}

function user_delete_from_pr2($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM pr2
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from pr2.');
    }
    
    return $result;
}

function user_delete_from_rank_tokens($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM rank_tokens
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from rank_tokens.');
    }
    
    return $result;
}

function user_delete_from_ratings($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM ratings
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from ratings.');
    }
    
    return $result;
}

function user_delete_from_recent_logins($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM recent_logins
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from recent_logins.');
    }
    
    return $result;
}

function user_delete_from_tokens($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM tokens
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from tokens.');
    }
    
    return $result;
}

function user_delete_from_users($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM users
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete user from users.');
    }
    
    return $result;
}
