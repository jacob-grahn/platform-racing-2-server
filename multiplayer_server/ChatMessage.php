<?php

class ChatMessage {

	public $from_id;
	public $message;
	
	public function __construct($from_id, $message){
		$this->from_id = $from_id;
		$this->message = $message;
	}
}