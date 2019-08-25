<?php

$SERVER_IP = '127.0.0.1';

$DB_ADDRESS = 'localhost';
$DB_PASS = '123';
$DB_USER = 'user';
$DB_NAME = 'pr2';
$DB_PORT = 1234;

$S3_SECRET = 'secret';
$S3_PASS = 'pass';

$PROCESS_PASS = 'abc';
$PROCESS_IP = '127.0.0.1';

$COMM_PASS = 'def';
$KONG_API_PASS = 'ghi';

$EMAIL_HOST = 'ssl://some.emailhost.com';
$EMAIL_PORT = 'port';
$EMAIL_USER = '2@2.com';
$EMAIL_PASS = 'pass';

$LEVEL_LIST_SALT = 'why does this exist?';
$PR2_HUB_API_PASS = 'is this still used?';

$CHANGE_EMAIL_KEY = 'why did I do this';
$CHANGE_EMAIL_IV = 'why oh why';

$LEVEL_SALT = 'fa';
$LEVEL_SALT_2 = 'ti';
$LEVEL_PASS_SALT = 'fa';
$LEVEL_PASS_KEY = 'so';
$LEVEL_PASS_IV = 'la';

$LOGIN_KEY = 'hello';
$LOGIN_IV = 'there';

$BLS_IP_PREFIX = 'no ddos plz';

$ALLOWED_CLIENT_VERSIONS = array('weeeee version', 'weeeee new version');
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
