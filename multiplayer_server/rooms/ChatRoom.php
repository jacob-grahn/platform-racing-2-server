<?php

namespace pr2\multi;

class ChatRoom extends Room
{

    private $keep_count = 18;
    private $chat_array = array();
    protected $room_name = 'chat_room';

    public $chat_room_name;


    public function __construct($chat_room_name)
    {
        $this->chat_room_name = htmlspecialchars($chat_room_name, ENT_QUOTES);

        global $chat_room_array;
        $chat_room_array[$this->chat_room_name] = $this;

        $this->chat_array = array_fill(0, $this->keep_count, '');
    }


    public function clear($mod)
    {
        global $pdo, $guild_id, $server_name;
        
        // preserve chatroom data
        $room_name = $this->chat_room_name;
        $old_chat_array = $this->chat_array;
    
        // send enough systemChat messages to clear the room
        foreach (range(0, 50) as $num) {
            $this->sendChat('systemChat` ');
            unset($num);
        }
        
        // notify the player
        foreach ($this->player_array as $player) {
            if ($player->user_id !== $mod->user_id) {
                $msg = 'A moderator has just cleared the chat log. '
                    .'Please re-enter the chatroom by clicking "Join Room".';
                $player->write("message`$msg");
            }
            $this->removePlayer($player);
        }
        
        // remove the chatroom
        $this->remove();
        
        // make a new one with the same name if a special chatroom
        if ($room_name === 'main' || $room_name === 'mod' || $room_name === 'admin') {
            $new_room = new ChatRoom($room_name);
            $mod_url = userify($mod, $mod->name);
            $new_room->sendChat("systemChat`$mod_url cleared the chatroom.");
        }
        
        // log mod action if on a public server and in main
        if ($guild_id === 0 && $room_name === 'main' && count($old_chat_array) > 0) {
            $log_chat = array();
            
            foreach ($old_chat_array as $key => $value) {
                // check to make sure there's a message
                if (!is_object($value)) {
                    unset($old_chat_array[$key]);
                    continue;
                }
                
                // extract the message
                $message_array = explode('`', $value->message);
                if ($message_array[0] === 'systemChat') {
                    array_push($log_chat, 'SYSTEM: ' . $message_array[1]);
                } elseif ($message_array[0] === 'chat') {
                    array_push($log_chat, 'Chat (' . $message_array[1] . '): ' . $message_array[3]);
                }
            }
            
            $chat_count = count($log_chat);
            if ($chat_count > 0) {
                $chat_str = join(' | ', $log_chat);
                $msg = "$mod->name cleared the main chatroom on $server_name from $mod->ip. "
                    ."{chat_count: $chat_count, chat_array: $chat_str}";
                mod_action_insert($pdo, $mod->user_id, $msg, $mod->user_id, $mod->ip);
            }
        }
        
        // tell the mod
        $mod->write('message`Chatroom successfully cleared. You can re-join by clicking "Join Room".');
    }
    
    
    public function whoIsHere()
    {
        $count = count($this->player_array);
        $str = "Currently in this chatroom ($count):"; // start the return string
        
        foreach ($this->player_array as $player) {
            $str .= "<br> - " . userify($player, $player->name);
        }
        
        // this should never happen (the person in the room is calling the function)
        if ($str === 'Currently in this chatroom:') {
            $str = 'No one is here. :(';
        }
        
        // send the string back
        return $str;
    }


    public function addPlayer($player)
    {
        Room::addPlayer($player);
        global $guild_id, $player_array;

        $welcome_message = 'systemChat`Welcome to chat room '.$this->chat_room_name.'! ';
        if (count($this->player_array) <= 1) {
            $welcome_message .= 'You\'re the only person here!';
        } else {
            $welcome_message .= 'There are ' . count($player_array) . ' people online, '
                . 'and ' . count($this->player_array) . ' people in this chat room.';
        }
        if ($this->chat_room_name === 'main' && $guild_id === 0) {
            $rules_link = urlify('https://pr2hub.com/rules', 'PR2 rules');
            $welcome_message .= " Before chatting, please read the $rules_link.";
        }
        $player->socket->write($welcome_message);

        foreach ($this->chat_array as $chat_message) {
            if ($chat_message !== '' && !$player->isIgnoredId($chat_message->from_id) && isset($player->socket)) {
                $player->socket->write($chat_message->message);
            }
        }
    }


    public function removePlayer($player)
    {
        Room::removePlayer($player);
        $keep_rooms = ['main', 'mod', 'admin'];
        if (count($this->player_array) <= 0 && !in_array($this->chat_room_name, $keep_rooms)) {
            $this->remove();
        }
    }


    public function sendChat($message, $user_id = -1)
    {
        $chat_message = new \stdClass();
        $chat_message->from_id = $user_id;
        $chat_message->message = $message;

        array_push($this->chat_array, $chat_message);
        $this->chat_array[0] = null;
        array_shift($this->chat_array);

        foreach ($this->player_array as $player) {
            if ($player->isIgnoredId($user_id) === false) {
                $player->socket->write($message);
            }
        }
    }


    public function remove()
    {
        global $chat_room_array;
        $chat_room_array[$this->chat_room_name] = null;
        unset($chat_room_array[$this->chat_room_name]);

        $this->chat_array = null;
        $this->room_name = null;
        $this->chat_room_name = null;

        parent::remove();
    }
}
