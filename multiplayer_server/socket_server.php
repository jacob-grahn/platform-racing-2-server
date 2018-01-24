<?php

class pr2_server extends socketServer {

	public static $last_read_time = 0;
	public static $happy_hour = false;
	public static $tournament = false;
	public static $no_prizes = false;
	public static $tournament_hat = 1;
	public static $tournament_speed = 65;
	public static $tournament_acceleration = 65;
	public static $tournament_jumping = 65;

	private static $last_time = 0;


	public function __construct($client_class, $bind_address = 0, $bind_port = 0, $domain = AF_INET, $type = SOCK_STREAM, $protocol = SOL_TCP) {
		parent::__construct($client_class, $bind_address, $bind_port, $domain, $type, $protocol);
		pr2_server::$last_time = time();
		pr2_server::$last_read_time = time();
	}


	public function on_timer() { //once every 10 seconds
		$elapsed = time() - pr2_server::$last_time;
		TemporaryItems::remove_expired();
		LocalBans::remove_expired();
		if($elapsed > 60*60) {
			pr2_server::$last_time = time();
			$this->consider_happy_hour();
		}
	}


	public function on_timer2() { //once every second
		LoiterDetector::check();
		$this->consider_shutting_down();
	}


	private function consider_happy_hour() {
		if(pr2_server::$happy_hour) {
			pr2_server::$happy_hour = false;
		}
		else if(rand(0, 36) == 36) {
			pr2_server::start_happy_hour();
		}
		echo "pr2_server::do_happy_hour - ".pr2_server::$happy_hour."\n";
	}


	private function consider_shutting_down() {
		$elapsed = time() - pr2_server::$last_read_time;
		if($elapsed > 60*5) {
			shutdown_server();
		}
	}


	public static function start_happy_hour() {
		if( !pr2_server::$tournament ) {
			pr2_server::$last_time = time();
			pr2_server::$happy_hour = true;
		}
	}
}



class pr2_server_client extends socketServerClient {

	public static $ip_array = array();
	private $subtracted_ip = false;

	private $rec_num = -1;
	public $last_user_action = 0;
	public $last_action = 0;
	public $login_id;
	public $process = false;
	public $ip;

	public function __construct($socket) {
		parent::__construct($socket);
		global $key;
		$this->key = $key;
		$time = time();
		$this->last_action = $time;
		$this->last_user_action = $time;
	}

	private function handle_request ($string) {
		try {
			$array = explode('`', $string);
			if ($this->process) {
				$call = $array[0];
				$function = "process_$call";
				array_splice($array, 0, 1);
				$data = join('`', $array);
			}
			else {
				$hash = $array[0];
				$send_num = $array[1];
				$call = $array[2];
				$function = "client_$call";

				array_splice($array, 0, 3);
				$data = join('`', $array);

				$str_to_hash = $this->key . $send_num.'`'.$call.'`'.$data;
				$local_hash = md5($str_to_hash);
				$sub_hash = substr($local_hash, 0, 3);

				if($sub_hash != $hash){
					$this->close();
					$this->on_disconnect();
					throw new Exception("the hash doesn't match. recieved: $hash, local: $sub_hash \n");
				}

				if($send_num > 2 && $send_num != $this->rec_num+1 && $send_num != 13) {
					$this->close();
					$this->on_disconnect();
					throw new Exception("a command was recieved out of order \n");
				}

				$this->rec_num = $send_num;
			}

			if (!function_exists($function)) {
				throw new Exception("$function is not a function");
			}

			$function($this, $data);

			$time = time();
			$this->last_action = $time;
			if($function != 'ping'){
				$this->last_user_action = $time;
			}
		}
		catch(Exception $e){
			echo 'Error: '.$e->getMessage()."\n";
		}
	}

	public function on_read(){

		pr2_server::$last_read_time = time();

		if($this->read_buffer == '<policy-file-request/>'.chr(0x00)) {
			$this->read_buffer = '';
			$this->write_buffer = '<cross-domain-policy><allow-access-from domain="*" to-ports="*" /></cross-domain-policy>'.chr(0x00);
			$this->do_write();
		}

		//breaks the buffer up into distinct commands
		//echo($this->read_buffer . "\n");
		$end_char = strpos($this->read_buffer,chr(0x04));
		while ($end_char !== FALSE){
			$info = substr($this->read_buffer, 0, $end_char);
			$this->handle_request($info);
			$this->read_buffer = substr($this->read_buffer, $end_char+1);
			$end_char = strpos($this->read_buffer,chr(0x04));
		}

		//prevent a data attack
		if(strlen($this->read_buffer) > 5000 && !$this->process){
			echo("\nKill read buffer -------------------------------\n");
			$this->read_buffer = '';
			$this->close();
			$this->on_disconnect();
		}
	}

	public function on_connect(){
		$ip = $this->remote_address;
		$this->ip = $ip;

		$ip_count = @pr2_server_client::$ip_array[$ip];
		if($ip_count == NULL) {
			$ip_count = 1;
		}
		else {
			$ip_count++;
		}
		pr2_server_client::$ip_array[$ip] = $ip_count;

		//echo "$ip ($ip_count)\n";

		if($ip_count > 5) {
			//echo("too many connections from this ip\n");
			$this->close();
			$this->on_disconnect();
			$this->disconnected = true;
		}

		else {
			$time = time();
			$this->last_action = $time;
			$this->last_user_action = $time;
			$this->disconnected = false;
		}
	}


	public function on_disconnect(){
		//echo "disconnect ".$this->remote_address."\n";
		if($this->disconnected == false){
			$this->disconnected = true;
			if(isset($this->player)){
				$this->player->remove();
				unset($this->player);
				$this->player = NULL;
			}
		}

		if($this->login_id != NULL) {
			global $login_array;
			$login_index[$this->login_id] = NULL;
		}
		$this->login_id = NULL;

		if(!$this->subtracted_ip) {
			$this->subtracted_ip = true;
			$ip = $this->remote_address;
			if(pr2_server_client::$ip_array[$ip] != NULL) {
				pr2_server_client::$ip_array[$ip]--;
				if(pr2_server_client::$ip_array[$ip] == 0) {
					unset(pr2_server_client::$ip_array[$ip]);
				}
			}
		}
	}

	public function on_write(){
		//echo "write \n";
	}

	public function on_timer(){
		if($this->last_action != 0){
			$time = time();
			$action_elapsed = $time - $this->last_action;
			$user_elapsed = $time - $this->last_user_action;
			if($action_elapsed > 35){
				$this->close();
				$this->on_disconnect();
			}
			if($user_elapsed > 60*30){
				$this->close();
				$this->on_disconnect();
			}
		}
	}

	public function get_player(){
		if(!isset($this->player)){
			throw new Exception("\n error: this socket does not have a player");
		}
		return $this->player;
	}
}



?>
