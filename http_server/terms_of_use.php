<?php
// phpcs:ignorefile

require_once HTTP_FNS . '/output_fns.php';

output_header('Terms of Use');

$rules_link = '<a href="https://pr2hub.com/rules">these rules ("The Rules")</a>';
$ip_link = '<a href="https://tools.keycdn.com/geo">IP Location Finder by KeyCDN</a>';
$pp_link = '<a href="https://paypal.com">PayPal</a>';

?>

<p>
    By accessing this site ("PR2 Hub") and/or Platform Racing 2 ("PR2"), you are agreeing to be bound by the terms and conditions of this Agreement.
    If you do not agree to the terms of this Agreement, do not use and/or access PR2 Hub and/or PR2.
</p>

<p>
    Access of PR2 Hub and/or PR2 is defined as using any service related to PR2
    (including but not limited to logging in, accessing any file or resource on the PR2 Hub Domain ("pr2hub.com"),
    purchasing items in the Vault of Magics (in-game store, "The VoM"), and purchasing Coins (as defined below)).
    Some data may be collected and analyzed (courtesy, in part, of <?= $ip_link ?>) in order to aid enforcement of
    The Rules, including (but not limited to) IP addresses. This data is never shared with third parties.
</p>

<p>
    The VoM licenses virtual items that may be rented or bought using digital currency ("Coins") licensed and distributed exclusively by PR2.
    These licenses can be revoked at any time under the terms of this agreement or at the sole discretion of PR2 or the Staff (as defined below).
    Payment for Coins purchases is collected via <?= $pp_link ?>. All sales are final.
    
</p>

<p>
    By accessing PR2 Hub and/or PR2, you agree to be bound by <?= $rules_link ?>.
    The Rules are evaluated and enforced by the appointed moderators and administrators of PR2 ("The Staff").
    You agree that failure to comply with The Rules may result in loss of access to intellectual property relating to
    PR2, PR2 Hub, and/or any of its premium services or items obtained via any method
    (including but not limited to The VoM, Coins,
    unlockable customizable items (hats, heads, bodies, feet, experience points, and epic upgrades), created levels,
    created guilds, and private messages).
    Violations of The Rules are evaluated and enforced at the sole discretion of The Staff.
</p>

<?php

output_footer();
