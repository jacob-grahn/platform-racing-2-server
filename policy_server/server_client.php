<?php

namespace jiggmin\ps;

class ServerClient extends \chabot\SocketServerClient
{

    public function onRead()
    {
        if ($this->read_buffer == '<policy-file-request/>'.chr(0x00)) {
            $this->read_buffer = '';
            $this->write(get_policy_file().chr(0x00));
        } elseif (strpos($this->read_buffer, 'status')  !== false) {
            $this->read_buffer = '';
            $this->write('ok'.chr(0x04));
        }
    }

    public function onConnect()
    {
        //echo "connect \n";
    }

    public function onDisconnect()
    {
        //echo "disconnect \n";
    }

    public function onWrite()
    {
        //echo "write \n";
    }

    public function onTimer()
    {
    }
}

function get_policy_file()
{
    return '<?xml version="1.0"?>
			<!DOCTYPE cross-domain-policy SYSTEM "/xml/dtds/cross-domain-policy.dtd">

			<!-- Policy file for xmlsocket://socks.example.com -->
			<cross-domain-policy>

			   <!-- This is a master socket policy file -->
			   <!-- No other socket policies on the host will be permitted -->
			   <site-control permitted-cross-domain-policies="master-only"/>

			   <allow-access-from domain="*" to-ports="9000-10000" />

			</cross-domain-policy>';
}
