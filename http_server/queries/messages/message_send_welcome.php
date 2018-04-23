<?php

function message_send_welcome($pdo, $name, $user_id)
{
    // compose a welcome pm
    $safe_name = htmlspecialchars($name);
    $welcome_message = "Welcome to Platform Racing 2, $safe_name!\n\n"
        ."<a href='https://grahn.io' target='_blank'><u><font color='#0000FF'>"
        ."Click here</font></u></a> to read about the latest Platform Racing news on my blog.\n\n"
        ."If you have any questions or comments, send me an email at "
        ."<a href='mailto:jacob@grahn.io?subject=Questions or Comments about "
        ."PR2' target='_blank'><u><font color='#0000FF'>jacob@grahn.io</font></u></a>.\n\n"
        ."Thanks for playing, I hope you enjoy.\n\n"
        ."- Jacob";

    // welcome them
    message_insert($pdo, $user_id, 1, $welcome_message, '0');
}
