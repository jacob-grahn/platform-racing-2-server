<?php

class ChatRoom extends Room {

<<<<<<< HEAD:server/rooms/ChatRoom.php
	private $chat_array = array();
	protected $room_name = 'chat_room';
	
	public $chat_room_name;
	
	
	public function __construct($chat_room_name){
		$this->chat_room_name = htmlspecialchars($chat_room_name);
		
		global $chat_room_array;
		$chat_room_array[htmlspecialchars($chat_room_name)] = $this;
		
		$this->chat_array = array_fill(0, 18, '');
	}
	
	
	public function add_player($player){
		Room::add_player($player);
		
=======
	private $keep_count = 18;
	private $chat_array = array();
	protected $room_name = 'chat_room';

	public $chat_room_name;


	public function __construct($chat_room_name){
		$this->chat_room_name = htmlspecialchars($chat_room_name);

		global $chat_room_array;
		$chat_room_array[htmlspecialchars($chat_room_name)] = $this;

		$this->chat_array = array_fill(0, $this->keep_count, '');
	}


	public function clear () {
		for ($i = 0; $i <= $this->keep_count; $i++) {
			$this->send_chat('systemChat` ', 0);
		}
	}


	public function add_player($player){
		Room::add_player($player);

>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/rooms/ChatRoom.php
		$welcome_message = 'systemChat`Welcome to chat room '.$this->chat_room_name.'! ';
		if(count($this->player_array) <= 1){
			$welcome_message .= 'You\'re the only person here!';
		}
		else{
			global $player_array;
			$welcome_message .= 'There are '.count($player_array).' people online, and '.count($this->player_array).' people in this chat room.';
		}
		$player->socket->write($welcome_message);

		foreach($this->chat_array as $chat_message){
			if($chat_message != '' && !$player->is_ignored_id($chat_message->from_id) && isset($player->socket)){
				$player->socket->write($chat_message->message);
			}
		}
	}
<<<<<<< HEAD:server/rooms/ChatRoom.php
	
	
=======


>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/rooms/ChatRoom.php
	public function remove_player($player){
		Room::remove_player($player);
		if(count($this->player_array) <= 0 && $this->chat_room_name != "main" && $this->chat_room_name != "mod" && $this->chat_room_name != "admin"){
			$this->remove();
		}
	}
<<<<<<< HEAD:server/rooms/ChatRoom.php
	
	
	public function send_chat($message, $user_id) {
		$chat_message = new ChatMessage($user_id, $message);
		
		array_push($this->chat_array, $chat_message);
		
		$this->chat_array[0] = NULL;
		array_shift($this->chat_array);
		
=======


	public function send_chat($message, $user_id) {
		$chat_message = new ChatMessage($user_id, $message);

		array_push($this->chat_array, $chat_message);

		$this->chat_array[0] = NULL;
		array_shift($this->chat_array);

>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/rooms/ChatRoom.php
		foreach($this->player_array as $player){
			if(!$player->is_ignored_id($user_id)){
				$player->socket->write($message);
			}
		}
	}
<<<<<<< HEAD:server/rooms/ChatRoom.php
	
	
=======


>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/rooms/ChatRoom.php
	public function get_record(){
		$str = '';
		foreach($this->chat_array as $chat_message){
			if($chat_message != ''){
				$str .= '<br/>'.$chat_message->message;
			}
		}
		return $str;
	}
<<<<<<< HEAD:server/rooms/ChatRoom.php
	
	
=======


>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/rooms/ChatRoom.php
	public function remove() {
		global $chat_room_array;
		$chat_room_array[$this->chat_room_name] = NULL;
		unset($chat_room_array[$this->chat_room_name]);
<<<<<<< HEAD:server/rooms/ChatRoom.php
		
		$this->chat_array = NULL;
		$this->room_name = NULL;
		$this->chat_room_name = NULL;
		
=======

		$this->chat_array = NULL;
		$this->room_name = NULL;
		$this->chat_room_name = NULL;

>>>>>>> 953edcb268f72150283df11849a05ce371d5d9ca:multiplayer_server/rooms/ChatRoom.php
		parent::remove();
	}
}
