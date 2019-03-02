<?php

$s3 = s3_connect();
$content = file_get_contents(WWW_ROOT . '/version.txt');
$s3->putObjectString($content, 'lookup.jiggmin.com', "platform-racing-2.txt");
