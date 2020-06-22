<?php

require_once HTTP_FNS . '/output_fns.php';

output_header('Terms of Use');

$rules_link = '<a href="https://pr2hub.com/rules">these rules ("The Rules")</a>';
$ip_link = '<a href="https://tools.keycdn.com/geo">IP Location Finder by KeyCDN</a>';
$kong_link = '<a href="https://kongregate.com/">Kongregate</a>';
$kong_ua_link = '<a href="https://www.kongregate.com/pages/terms">Kongregate\'s User Agreement</a>';
$kong_feedback_link = '<a href="https://www.kongregate.com/feedbacks/new">contact Kongregate</a>';

echo 'By accessing this site ("PR2 Hub") and/or Platform Racing 2 ("PR2"), '
    .'you are agreeing to be bound by the terms and conditions of this Agreement. '
    .'If you do not agree to the terms of this Agreement, do not use and/or access PR2 Hub and/or PR2.'
    .'<br><br>';

echo "By accessing PR2 Hub and/or PR2, you agree to be bound by $rules_link. "
    .'Access of PR2 Hub and/or PR2 is defined as using any service related to PR2 '
    .'(including but not limited to logging in, accessing any file or resource on the PR2 Hub Domain ("pr2hub.com"), '
    .'and purchasing items in the Vault of Magics (in-game store, "The VoM")). Some data may be collected and analyzed '
    ."(courtesy, in part, of $ip_link) in order to aid enforcement of The Rules, "
    .'including (but not limited to) IP addresses. This data is never shared with third parties.<br><br>';

echo 'You agree that failure to comply with The Rules may result in '
    .'loss of access to intellectual property relating to PR2, PR2 Hub, '
    .'and/or any of its premium services or items obtained via any method '
    .'(including but not limited to The VoM, unlockable customizable items '
    .'(hats, heads, bodies, feet, experience points, and epic upgrades), created levels, '
    .'created guilds, and private messages).<br><br>';

echo 'The VoM sells premium items that may be rented or bought '
    ."using digital currency (\"Kreds\") sold and distributed exclusively by $kong_link. "
    .'Kongregate has its own set of laws governing the purchase and use of Kreds. '
    ."These laws can be found in $kong_ua_link. "
    ."For support regarding the purchase and use of Kreds, $kong_feedback_link. ";

output_footer();
