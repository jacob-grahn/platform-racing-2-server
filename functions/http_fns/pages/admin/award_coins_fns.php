<?php


// page
function output_form($name = '')
{
    $name = $name === false ? '' : $name;

    echo '<form action="award_coins.php" method="post">';

    echo "Award Coins<br><br>";

    echo "User Name: <input type='text' name='user_name' maxlength='50' value='$name'> "
        .'(user name of recipient)<br>';
    echo 'Coins: <input type="text" name="num_coins" maxlength="10"> '
        .'(number of coins to award, max: 500 per award, 20 awards per day)<br>';
    echo 'Comment: <input type="text" name="comment" maxlength="255"> '
        .'(short comment explaining the award, max: 255 characters)<br>';

    echo '<input type="hidden" name="action" value="award">';

    echo '<br>';
    echo '<input type="submit" value="Award Coins">&nbsp;(no confirmation!)';
    echo '</form>';
}


// add contest function
function award_coins($pdo, $admin)
{
    // make some nice variables
    $name = find('user_name');
    $num_coins = (int) find('num_coins');
    $comment = find('comment');

    // sanity check: correct number of coins?
    if ($num_coins <= 0 || $num_coins > 500) {
        throw new Exception('You can only award a maximum of 500 coins at a time.');
    }

    // sanity checks
    $user = user_select_by_name($pdo, $name, true);
    if ($user === false) { // does the recipient exist?
        throw new Exception('Could not find a user with that name.');
    } elseif ((int) $user->power === 0) { // is the recipient a guest?
        throw new Exception('Guests can\'t have coins.');
    } elseif ($user->coins + $num_coins > 16777215) { // crazy amount of coins? (this is ridiculous. I realize that.)
        throw new Exception('Awarding this number of coins would exceed the maximum amount of coins per account.');
    }

    // more rate limiting
    rate_limit('award-coins-'.$admin->user_id, 86400, 20, 'You may only award coins 20 times per day.');
    rate_limit('award-coins-'.$admin->user_id, 60, 2);
    rate_limit('award-coins-'.$admin->user_id, 5, 1);

    // update coins
    $coins_before = (int) $user->coins;
    $new_coins_total = number_format($coins_before + $num_coins);
    user_update_coins($pdo, $user->user_id, $num_coins);
    $inserted_order_id = vault_coins_comp_order_insert($pdo, $user->user_id, $coins_before, $num_coins, $comment);

    // log in admin action log
    if ($inserted_order_id != false) {
        // log the action in the admin log
        $ip = get_ip();
        $msg = "$admin->name awarded $num_coins coins to $user->name from $ip. {" .
            "pr2_purchase_id: $inserted_order_id, ".
            "recipient_id: $user->user_id, ".
            "coins_before: $coins_before, ".
            "coins_awarded: $num_coins, ".
            "new_coins_total: $new_coins_total, ".
            "comment: $comment}";
        admin_action_insert($pdo, $admin->user_id, $msg, 'coins-award', $ip);
    }

    // compose and send a message to the recipient
    $safe_recip_name = htmlspecialchars($user->name, ENT_QUOTES);
    $safe_admin_name = htmlspecialchars($admin->name, ENT_QUOTES);
    $recip_message = "Dear $safe_recip_name,\n\n"
                    ."I'm pleased to inform you that you have been awarded $num_coins coins! "
                    ."You now have [b]$new_coins_total coins[/b] in your account.\n\n"
                    ."If you believe this is an error, please reply to this PM to request more information. "
                    ."Thanks for playing PR2, and congratulations!\n\n"
                    ." - $safe_admin_name";
    message_insert($pdo, $user->user_id, $admin->user_id, $recip_message, $ip);

    // output the page
    output_header('Award Coins', true, true);
    $url_recip_name = urlencode($user->name);
    echo "Great success! $num_coins coins were awarded to $safe_recip_name. They now have $new_coins_total coins.";
    echo "<br><br>";
    echo "<a href='award_coins.php'>&lt;- Award More Coins</a><br>";
    echo "<a href='player_deep_info.php?name1=$url_recip_name'>-&gt; View Player Deep Info</a>";
}
