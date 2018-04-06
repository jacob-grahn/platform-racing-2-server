<?php

function pr2_select_true_rank($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT pr2.rank AS rank,
               rank_tokens.used_tokens AS tokens
          FROM pr2
          LEFT JOIN rank_tokens ON rank_tokens.user_id = pr2.user_id
         WHERE pr2.user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result == false) {
        throw new Exception("Could not perform query pr2_select_true_rank.");
    }
    
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($result === false || empty($result)) {
        throw new Exception("Could not find a user with that ID.");
    }
    
    $rank = (int) $result->rank;
    $tokens = (int) $result->tokens;
    $true_rank = $rank + $tokens;
    
    return $true_rank;
}
