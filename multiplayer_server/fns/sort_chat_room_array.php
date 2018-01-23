<?php


//--- sort the chat rooms by how many users are in them ---------------------------------
function sort_chat_room_array($a, $b){
    if (count($a->player_array) == count($b->player_array)) {
        return 0;
    }
    return (count($a->player_array) > count($b->player_array)) ? -1 : 1;
}


?>
