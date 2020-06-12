<?php


function recent_logins_count_by_user($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
          FROM recent_logins
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not check the total number of logins for this user.");
    }

    $count = $stmt->fetchColumn();

    if ((int) $count == 0 || $count == false || empty($count)) {
        return 0;
    }

    return $count;
}


function recent_logins_insert($pdo, $user_id, $ip, $country_code)
{
    $stmt = $pdo->prepare('
        INSERT INTO recent_logins
           SET user_id = :user_id,
               ip = :ip,
               country = :country_code,
               date = NOW()
        ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':country_code', $country_code, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        return false;
    }

    return true;
}


function recent_logins_select($pdo, $user_id, $suppress_error = false, $start = 0, $count = 100)
{
    $start = (int) $start;
    $count = (int) $count;

    $stmt = $pdo->prepare('
        SELECT *
          FROM recent_logins
         WHERE user_id = :user_id
         ORDER BY date DESC
         LIMIT :start , :count
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception("Could not perform query recent_logins_select.");
        } else {
            return false;
        }
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function recent_logins_select_count_missing_country_by_ip($pdo, $ip)
{
    $stmt = $pdo->prepare('
        SELECT count(ip) as count
          FROM recent_logins
         WHERE country = "?"
           AND ip = :ip
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query recent_logins_select_count_missing_country_by_ip.');
    }

    $data = $stmt->fetch(PDO::FETCH_OBJ);
    return !empty($data) && isset($data->count) ? (int) $data->count : 0;
}


function recent_login_select_country_from_ip($pdo, $ip)
{
    $stmt = $pdo->prepare('
        SELECT country
          FROM recent_logins
         WHERE country != "?"
           AND ip = :ip
         ORDER BY date DESC
         LIMIT 1
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query recent_login_select_country_from_ip.');
    }

    $data = $stmt->fetch(PDO::FETCH_OBJ);
    return !empty($data) && isset($data->country) ? $data->country : '?';
}


function recent_logins_update_missing_country($pdo, $ip, $country_code)
{
    $stmt = $pdo->prepare('
        UPDATE recent_logins
           SET country = :country_code
         WHERE ip = :ip
           AND country = "?"
    ');
    $stmt->bindValue(':country_code', $country_code, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query recent_logins_update_missing_country.');
    }
}


function recent_logins_user_select_by_ip($pdo, $ip)
{
    $count = (int) $count;
    $stmt = $pdo->prepare('
        SELECT DISTINCT user_id
          FROM recent_logins
         WHERE ip = :ip
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        return false;
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
