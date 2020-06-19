<?php


function guild_count_members($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
          FROM users
         WHERE guild = :guild_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not count the number of members in guild $guild_id.");
    }

    $count = $stmt->fetchColumn();

    if ((int) $count == 0 || $count == false || empty($count)) {
        return 0;
    }

    return $count;
}


function guild_delete($pdo, $guild_id)
{
    guild_delete_from_guilds($pdo, $guild_id);
    guild_delete_from_users($pdo, $guild_id);
    guild_delete_from_gp($pdo, $guild_id);
}


// consider calling with guild_delete!
function guild_delete_from_guilds($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM guilds
         WHERE guild_id = :guild_id
         LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not perform query guild_delete_from_guilds.");
    }

    return $result;
}


// consider calling with guild_delete!
function guild_delete_from_users($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        UPDATE users
        SET guild = 0
        WHERE guild = :guild_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not perform query guild_delete_from_users.");
    }

    return $result;
}


// consider calling with guild_delete!
function guild_delete_from_gp($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM gp
        WHERE guild_id = :guild_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query guild_delete_from_gp.');
    }

    return $result;
}


function guild_increment_gp($pdo, $guild_id, $gp)
{
    $stmt = $pdo->prepare('
        UPDATE guilds
           SET gp_today = gp_today + :gp,
               gp_total = gp_total + :gp,
               active_date = NOW()
         WHERE guild_id = :guild_id
         LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':gp', $gp, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record earned GP for guild #$guild_id.');
    }

    return $result;
}


function guild_increment_member($pdo, $guild_id, $number)
{
    $number = (int) $number;

    // determine correct operation
    if ($number < 0) {
        $number = abs($number);
        $sign = '-';
    } elseif ($number > 0) {
        $sign = '+';
    } else {
        return true;
    }

    $stmt = $pdo->prepare("
        UPDATE guilds
           SET member_count = member_count $sign :number,
               active_date = NOW()
         WHERE guild_id = :guild_id
         LIMIT 1
    ");
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':number', $number, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not increment guild member count in guild $guild_id.");
    }

    return $result;
}


function guild_insert($pdo, $owner_id, $guild_name, $emblem, $note)
{
    $stmt = $pdo->prepare('
        INSERT INTO guilds
        SET owner_id = :owner_id,
            guild_name = :guild_name,
            emblem = :emblem,
            note = :note,
            creation_date = NOW(),
            active_date = NOW(),
            member_count = 1
    ');
    $stmt->bindValue(':owner_id', $owner_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_name', $guild_name, PDO::PARAM_STR);
    $stmt->bindValue(':emblem', $emblem, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not create guild.');
    }

    return $pdo->lastInsertId();
}


function guild_name_to_id($pdo, $guild_name, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT guild_id
          FROM guilds
         WHERE guild_name = :guild_name
         LIMIT 1
    ');
    $stmt->bindValue(':guild_name', $guild_name, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query guild_name_to_id.');
    }

    $guild = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($guild)) {
        if ($suppress_error === false) {
            throw new Exception('Could not find a guild with that name.');
        } else {
            return false;
        }
    }

    return $guild->guild_id;
}


function guild_select_active_member_count($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*) AS active_member_count
        FROM users
        WHERE users.guild = :guild_id
        AND users.time > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY);
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not determine active guild members.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    if ($row === false) {
        return 0;
    }

    return $row->active_member_count;
}


function guild_select_by_name($pdo, $guild_name)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM guilds
        WHERE guild_name = :guild_name
        LIMIT 1
    ');
    $stmt->bindValue(':guild_name', $guild_name, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query guild_select_by_name.');
    }

    $guild = $stmt->fetch(PDO::FETCH_OBJ);

    if ($guild === false) {
        throw new Exception('Could not find a guild with that name.');
    }

    return $guild;
}


function guild_select_members($pdo, $guild_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT u.user_id, u.name, u.power, u.trial_mod, p.rank, gp.gp_today, gp.gp_total
        FROM users u LEFT JOIN pr2 p ON u.user_id = p.user_id
        LEFT JOIN gp ON (u.user_id = gp.user_id AND gp.guild_id = :guild_id)
        WHERE u.guild = :guild_id
        ORDER BY gp.gp_today DESC
        LIMIT 0, 101
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not select guild members.');
        } else {
            return false;
        }
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function guild_select_owner_id($pdo, $guild_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT owner_id
        FROM guilds
        WHERE guild_id = :guild_id
        LIMIT 1;
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not perform query guild_select_owner_id.');
        }
        return false;
    }

    $guild = $stmt->fetch(PDO::FETCH_OBJ);
    if (empty($guild)) {
        throw new Exception('Could not find a guild with that ID.');
    }

    return $guild->owner_id;
}


function guild_select($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM guilds
        WHERE guild_id = :guild_id
        LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query guild_select.');
    }

    $guild = $stmt->fetch(PDO::FETCH_OBJ);
    if ($guild === false) {
        throw new Exception('Could not find a guild with that ID.');
    }

    return $guild;
}


function guild_update($pdo, $guild_id, $guild_name, $emblem, $note, $owner_id, $member_count = null)
{
    // check for member count passed to this function
    $memcount_sql = '';
    if ($member_count != null) {
        $member_count = (int) $member_count;
        $memcount_sql = 'member_count = :member_count,';
    }

    // do it
    $stmt = $pdo->prepare("
        UPDATE guilds
           SET guild_name = :name,
               $memcount_sql
               emblem = :emblem,
               note = :note,
               owner_id = :owner_id,
               active_date = NOW()
         WHERE guild_id = :guild_id
         LIMIT 1
    ");
    $stmt->bindValue(':name', $guild_name, PDO::PARAM_STR);
    $stmt->bindValue(':emblem', $emblem, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':owner_id', $owner_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    if ($member_count != null) {
        $stmt->bindValue(':member_count', $member_count, PDO::PARAM_INT);
    }
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update guild.');
    }

    return $result;
}


function guilds_reset_gp_today($pdo)
{
    $result = $pdo->exec('UPDATE guilds SET gp_today = 0');

    if ($result === false) {
        throw new Exception('Could not reset all guilds\' gp_today column.');
    }

    return $result;
}


function guilds_select_by_most_gp_today($pdo)
{
    $stmt = $pdo->prepare('
        SELECT guild_id, guild_name, gp_today, gp_total
          FROM guilds
         WHERE member_count > 0
            OR gp_today > 0
            OR gp_total > 100
         ORDER BY gp_today DESC, gp_total DESC
         LIMIT 50
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query guilds_select_by_most_gp_today.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
