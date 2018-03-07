<?php

class server extends socketServer
{
}

class server_client extends socketServerClient
{
    
    public function on_read()
    {
        //echo "recieved: ".$this->read_buffer."\n";
        if($this->read_buffer == '<policy-file-request/>'.chr(0x00)) {
            $this->read_buffer = '';
            //echo "writing... \n";
            $this->write(get_policy_file().chr(0x00));
        }
        
        else if(strpos($this->read_buffer, 'status')  !== false) {
            $this->read_buffer = '';
            $this->write('ok'.chr(0x04));
        }
    }

    public function on_connect()
    {
        //echo "connect \n";
    }

    public function on_disconnect()
    {
        //echo "disconnect \n";
    }

    public function on_write()
    {
        //echo "write \n";
    }

    public function on_timer()
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

    
?>
