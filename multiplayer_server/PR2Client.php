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

    public function __construct($socket)
    {
        parent::__construct($socket);
        $time = time();
        $this->last_action = $time;
        $this->last_user_action = $time;
    }

    private function handleRequest($string)
    {
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
        if ($this->read_buffer === '<policy-file-request/>'.chr(0x00)) {
            $this->read_buffer = '';
            $this->write_buffer = '<cross-domain-policy>'.
                '<allow-access-from domain="*" to-ports="*" />'.
                '</cross-domain-policy>'.chr(0x00);
            $this->doWrite();
        }

        // breaks the buffer up into distinct commands
        $end_char = strpos($this->read_buffer, chr(0x04));
        while ($end_char !== false) {
            $info = substr($this->read_buffer, 0, $end_char);
            $this->handleRequest($info);
            $this->read_buffer = substr($this->read_buffer, $end_char+1);
            $end_char = strpos($this->read_buffer, chr(0x04));
        }

        // prevent a data attack
        if (strlen($this->read_buffer) > 5000 && !$this->process) {
            $this->read_buffer = '';
            output(" --- KILLED READ BUFFER --- ");
            $this->close();
            $this->onDisconnect();
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
        $buffer .= chr(0x04);
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
