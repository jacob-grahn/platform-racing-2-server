<?php

$BLS_IP_PREFIX = 'test';
$SERVER_IP = '127.0.0.1';

$DEBUG_MODE = false; // always set to false in production environments

$DB_ADDRESS = 'mysql';
$DB_PASS = 'pr2';
$DB_USER = 'pr2';
$DB_NAME = 'pr2';
$DB_PORT = 3306;

$S3_SECRET = 'secret';
$S3_PASS = 'pass';

$PROCESS_PASS = 'abc';
$PROCESS_IP = '127.0.0.1';

$COMM_PASS = 'def';

$EMAIL_HOST = 'ssl://some.emailhost.com';
$EMAIL_PORT = 'port';
$EMAIL_USER = '2@2.com';
$EMAIL_PASS = 'pass';

$PR2_HUB_API_KEY = 'test';
$PR2_HUB_API_ALLOWED_IPS = [
    'an ip',
    'another ip'
];

$IP_API_ENABLED = true;
$IP_API_KEY_1 = 'my secret key';
$IP_API_KEY_2 = 'my super secret key';
$IP_API_LINK_PRE = 'a link pre';
$IP_API_LINK_SUF = 'a link suf';
$IP_API_SCORE_MIN = /* over */ 9000;
$IP_API_LINK_2 = 'another link';

$VAULT_TITLE = '';

$BANNED_IP_PREFIXES = [
    '127.0.0.',
    '192.168.0.'
];

$KONG_API_PASS = 'ghi';

$PAYPAL_SANDBOX = false; // always set to false in production environments
$PAYPAL_API_ENDPOINT = $PAYPAL_SANDBOX ? 'https://api.sandbox.paypal.com' : 'https://api.paypal.com';
$PAYPAL_CLIENT_ID = $PAYPAL_SANDBOX ? 'sandbox client id' : 'production client id';
$PAYPAL_SECRET = $PAYPAL_SANDBOX ? 'sandbox secret' : 'production secret';

$PAYPAL_DATA_KEY = 'cookie';
$PAYPAL_DATA_IV = 'monster';

$URL_SALT = 'haha';
$URL_KEY = 'encryption';
$URL_IV = 'go brrr';

$LEVEL_LIST_SALT = 'why does this exist?';
$LEVEL_SALT = 'fa';
$LEVEL_SALT_2 = 'ti';
$LEVEL_PASS_SALT = 'fa';
$LEVEL_PASS_KEY = 'so';
$LEVEL_PASS_IV = 'la';

$LOGIN_KEY = 'hello';
$LOGIN_IV = 'there';

$ACCOUNT_CHANGE_KEY = 'why did I do this';
$ACCOUNT_CHANGE_IV = 'why oh why';

$ALLOWED_CLIENT_VERSIONS = array('weeeee version', 'weeeee new version');
$FALLBACK_ADDRESSES = array($SERVER_IP);
$TRUSTED_REFS = [ // trusted referrers for the pr2 client
    'http://pr2hub.com/', // pr2hub
    'https://pr2hub.com/', // pr2hub
    'http://www.pr2hub.com/', // pr2hub
    'https://www.pr2hub.com/', // pr2hub
    'http://cdn.jiggmin.com/', // jv
    'http://chat.kongregate.com/', // kong
    'http://external.kongregate-games.com/gamez/', // kong
    'http://game10110.konggames.com/games/Jiggmin/platform-racing-2', // kong
    'http://uploads.ungrounded.net/439000/', // newgrounds
    'https://jiggmin2.com/games/platform-racing-2', // jv2
    'http://naxxol.github.io/' // advanced LE
];
