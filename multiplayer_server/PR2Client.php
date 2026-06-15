<?php

namespace pr2\multi;

class PR2Client extends \chabot\SocketServerClient
{

    public static $ip_array = array();
    private $subtracted_ip = false;

    private $rec_num = -1;
    private $send_num = 0;
    public $last_user_action = 0;
    public $last_action = 0;
    public $login_id;
    public $process = false;
    public $ip;
    public $id;
    public $player;

    // transport: null until sniffed, then 'raw' or 'ws'
    private $transport = null;
    // websocket transport state
    private $ws_ready = false;       // handshake completed
    private $ws_app_buffer = '';     // decoded application bytes awaiting processing
    private $ws_opcode = WebSocket::OP_TEXT; // opcode to mirror back to the client

    public function __construct($socket)
    {
        parent::__construct($socket);
        $this->id = spl_object_id($socket);
        $time = time();
        $this->last_action = $time;
        $this->last_user_action = $time;
        output(" --- Creating PR2Client --- ");
    }

    protected function handleRequest($string)
    {
        global $verbose;
        if ($verbose === true) {
            output('READ: ' . $string);
        }
        try {
            $array = explode('`', $string);
            if ($this->process) {
                $call = $array[0];
                $function = "process_$call";
                array_splice($array, 0, 1);
                $data = join('`', $array);
            } else {
                $hash = $array[0];
                $send_num = (int) $array[1];
                $call = $array[2];
                $function = "client_$call";

                array_splice($array, 0, 3);
                $data = join('`', $array);

                $str_to_hash = SALT . $send_num . '`' . $call . '`' . $data;
                $local_hash = md5($str_to_hash);
                $sub_hash = substr($local_hash, 0, 3);

                if ($sub_hash !== $hash) {
                    $this->close();
                    $this->onDisconnect();
                    throw new \Exception("The received hash doesn't match. Recieved: $hash | Local: $sub_hash");
                }

                if ($send_num > 2 && $send_num !== $this->rec_num + 1 && $send_num !== 13) {
                    $this->close();
                    $this->onDisconnect();
                    throw new \Exception('A command was recieved out of order.');
                }

                $this->rec_num = $send_num;
            }

            if (!function_exists($function)) {
                throw new \Exception("$function is not a function.");
            }

            $function($this, $data);

            $time = time();
            $this->last_action = $time;
            if ($function !== 'client_ping') {
                $this->last_user_action = $time;
            }
        } catch (\Exception $e) {
            output('Error: '.$e->getMessage());
        }
    }

    public function onRead()
    {
        // figure out the transport from the first bytes received
        if ($this->transport === null) {
            $this->transport = WebSocket::sniff($this->read_buffer);
            if ($this->transport === null) {
                return; // not enough bytes to decide yet
            }
        }

        if ($this->transport === 'ws') {
            $this->onReadWebSocket();
        } else {
            $this->onReadRaw();
        }
    }

    // legacy Flash / raw socket transport
    private function onReadRaw()
    {
        if ($this->read_buffer === '<policy-file-request/>'.chr(0x00)) {
            $this->read_buffer = '';
            $this->write_buffer = '<cross-domain-policy>'.
                '<allow-access-from domain="*" to-ports="*" />'.
                '</cross-domain-policy>'.chr(0x00);
            $this->doWrite();
        }

        $this->processBuffer($this->read_buffer);

        // prevent a data attack
        if (strlen($this->read_buffer) > 5000 && !$this->process) {
            $this->read_buffer = '';
            output(" --- KILLED READ BUFFER --- ");
            $this->close();
            $this->onDisconnect();
        }
    }

    // websocket transport: a transparent wrapper around the same protocol
    private function onReadWebSocket()
    {
        if (!$this->ws_ready) {
            $response = WebSocket::buildHandshakeResponse($this->read_buffer);
            if ($response === null) {
                return; // wait for the full HTTP header
            }
            // consume the header and send the 101 response (raw, un-framed)
            $header_end = strpos($this->read_buffer, "\r\n\r\n") + 4;
            $this->read_buffer = substr($this->read_buffer, $header_end);
            $this->ws_ready = true;
            $this->write_buffer .= $response;
            $this->doWrite();
        }

        // decode complete frames out of the read buffer
        $frames = WebSocket::decode($this->read_buffer, $consumed);
        if ($consumed > 0) {
            $this->read_buffer = substr($this->read_buffer, $consumed);
        }

        foreach ($frames as $frame) {
            list($opcode, $payload) = $frame;
            switch ($opcode) {
                case WebSocket::OP_TEXT:
                case WebSocket::OP_BINARY:
                    $this->ws_opcode = $opcode; // mirror the client's framing
                    $this->ws_app_buffer .= $payload;
                    break;
                case WebSocket::OP_CONTINUATION:
                    $this->ws_app_buffer .= $payload;
                    break;
                case WebSocket::OP_PING:
                    $this->write_buffer .= WebSocket::encode($payload, WebSocket::OP_PONG);
                    $this->doWrite();
                    break;
                case WebSocket::OP_CLOSE:
                    $this->close();
                    $this->onDisconnect();
                    return;
                case WebSocket::OP_PONG:
                default:
                    break;
            }
        }

        $this->processBuffer($this->ws_app_buffer);

        // prevent a data attack
        $buffered = strlen($this->ws_app_buffer) + strlen($this->read_buffer);
        if ($buffered > 5000 && !$this->process) {
            $this->ws_app_buffer = '';
            $this->read_buffer = '';
            output(" --- KILLED READ BUFFER --- ");
            $this->close();
            $this->onDisconnect();
        }
    }

    // breaks a buffer up into distinct chr(0x04) delimited commands
    private function processBuffer(&$buffer)
    {
        $end_char = strpos($buffer, chr(0x04));
        while ($end_char !== false) {
            $info = substr($buffer, 0, $end_char);
            $this->handleRequest($info);
            $buffer = substr($buffer, $end_char + 1);
            $end_char = strpos($buffer, chr(0x04));
        }
    }

    public function write($buffer, $length = 4096)
    {
        if (!$this->process) {
            $buffer = $this->send_num . '`' . $buffer;
            $str_to_hash = SALT . $buffer;
            $hash_bit = substr(md5($str_to_hash), 0, 3);
            $buffer = $hash_bit . '`' . $buffer;
        }
        global $verbose;
        if ($verbose === true) {
            output('WRITE: ' . $buffer);
        }
        $buffer .= chr(0x04);
        if ($this->transport === 'ws') {
            $buffer = WebSocket::encode($buffer, $this->ws_opcode);
        }
        parent::write($buffer, $length);
        $this->send_num++;
    }

    public function onConnect()
    {
        $ip = $this->remote_address;
        $this->ip = $ip;

        $ip_count = @PR2Client::$ip_array[$ip];
        $ip_count = !isset($ip_count) ? 1 : ++$ip_count;
        PR2Client::$ip_array[$ip] = $ip_count;

        if ($ip_count > 5) {
            $this->close();
            $this->onDisconnect();
        } else {
            $time = time();
            $this->last_action = $time;
            $this->last_user_action = $time;
        }
    }


    public function onDisconnect()
    {
        if (isset($this->player)) {
            $this->player->socket = null;
            $this->player->remove();
            $this->player = null;
        }

        if (isset($this->login_id)) {
            global $login_array;
            $login_array[$this->login_id] = null;
            $this->login_id = null;
        }

        if (!$this->subtracted_ip) {
            $this->subtracted_ip = true;
            $ip = $this->remote_address;
            if (isset(PR2Client::$ip_array[$ip])) {
                PR2Client::$ip_array[$ip]--;
                if (PR2Client::$ip_array[$ip] <= 0) {
                    unset(PR2Client::$ip_array[$ip]);
                }
            }
        }
    }

    // once every 2 seconds
    public function onTimer()
    {
        if ($this->last_action !== 0) {
            $time = time();
            $action_elapsed = $time - $this->last_action;
            $user_elapsed = $time - $this->last_user_action;
            if ($action_elapsed > 60 || $user_elapsed > 1800) {
                $this->close();
                $this->onDisconnect();
            }
        }
    }

    public function getPlayer()
    {
        if (!isset($this->player)) {
            throw new \Exception('This socket does not have a player.');
        }
        return $this->player;
    }
}
