<?
/*
phpSocketDaemon 1.0
Copyright (C) 2006 Chris Chabot <chabotc@xs4all.nl>
See http://www.chabotc.nl/ for more information

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
*/
abstract class socketClient extends socket {
	public $remote_address = null;
	public $remote_port    = null;
	public $connecting     = false;
	public $disconnected   = false;
	public $read_buffer    = '';
	public $write_buffer   = '';
	protected $key = '';
	private $send_num = 0;
	
	
	/*public function __construct($bind_address = 0, $bind_port = 0, $domain = AF_INET, $type = SOCK_STREAM, $protocol = SOL_TCP) {
		parent::__construct($bind_address, $bind_port, $domain, $type, $protocol);
	}*/

	
	public function connect($remote_address, $remote_port)
	{
		$this->connecting = true;
		try {
			parent::connect($remote_address, $remote_port);
		} catch (socketException $e) {
			echo "Caught exception: ".$e->getMessage()."\n";
		}
	}

	
	public function write($buffer, $length = 4096)
	{	
		if(!$this->process){
			$buffer = $this->send_num.'`'.$buffer;
			$str_to_hash = $this->key . $buffer;
			$hash_bit = substr(md5($str_to_hash), 0, 3);
			$buffer = $hash_bit.'`'.$buffer;
		}
		
		$buffer .= chr(0x04);
		
		//echo "send: $buffer \n";
		//echo "Hash made from: $str_to_hash \n";
		
		$this->write_buffer .= $buffer;
		$this->do_write();
		
		$this->send_num++;
	}

	public function do_write()
	{
		$length = strlen($this->write_buffer);
		try {
			$this->on_write();
			$written = parent::write($this->write_buffer, $length);
			if ($written < $length) {
				$this->write_buffer = substr($this->write_buffer, $written);
			} else {
				$this->write_buffer = '';
			}
			return true;
		} catch (socketException $e) {
			$old_socket         = (int)$this->socket;
			$this->close();
			$this->socket       = $old_socket;
			$this->on_disconnect();
			return false;
		}
		return false;
	}

	public function read($length = 4096)
	{
		try {
			$this->read_buffer .= parent::read($length);
			$this->on_read();
		} catch (socketException $e) {
			$old_socket         = (int)$this->socket;
			$this->close();
			$this->socket       = $old_socket;
			$this->on_disconnect();
		}
	}

	public function on_connect() {}
	public function on_disconnect() {}
	public function on_read() {}
	public function on_write() {}
	public function on_timer() {}
}
