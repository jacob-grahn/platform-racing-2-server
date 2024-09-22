<?php
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
namespace chabot;

abstract class Socket
{
    public $socket;
    public $bind_address;
    public $bind_port;
    public $domain;
    public $type;
    public $protocol;
    public $local_addr;
    public $local_port;
    public $read_buffer    = '';
    public $write_buffer   = '';

    public function __construct(
        $bind_address = 0,
        $bind_port = 0,
        $domain = AF_INET,
        $type = SOCK_STREAM,
        $protocol = SOL_TCP
    ) {
        $this->bind_address = $bind_address;
        $this->bind_port    = $bind_port;
        $this->domain       = $domain;
        $this->type         = $type;
        $this->protocol     = $protocol;
        if (($this->socket = @socket_create($domain, $type, $protocol)) === false) {
            throw new \Exception("Could not create socket: ".socket_strerror(socket_last_error($this->socket)));
        }
        if (!@socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            throw new \Exception("Could not set SO_REUSEADDR: ".$this->getError());
        }
        if (!@socket_bind($this->socket, $bind_address, $bind_port)) {
            throw new \Exception(
                "Could not bind socket to [$bind_address - $bind_port]: "
                .socket_strerror(socket_last_error($this->socket))
            );
        }
        if (!@socket_getsockname($this->socket, $this->local_addr, $this->local_port)) {
            throw new \Exception(
                "Could not retrieve local address & port: "
                .socket_strerror(socket_last_error($this->socket))
            );
        }
        $this->setNonBlock(true);
    }

    public function __destruct()
    {
        if ($this->socket instanceof \Socket) {
            $this->close();
        }
    }

    public function getError()
    {
        $error = socket_strerror(socket_last_error($this->socket));
        socket_clear_error($this->socket);
        return $error;
    }

    public function close()
    {
        if ($this->socket instanceof \Socket) {
            @socket_shutdown($this->socket, 2);
            @socket_close($this->socket);
        }
        $this->socket = (string)$this->socket;
    }

    public function write($buffer, $length = 4096)
    {
        if (($ret = @socket_write($this->socket, $buffer, $length)) === false) {
            throw new \Exception("Could not write to socket: ".$this->getError());
        }
        return $ret;
    }

    public function read($length = 4096)
    {
        if (($ret = @socket_read($this->socket, $length, PHP_BINARY_READ)) == false) {
            throw new \Exception("Could not read from socket: ".$this->getError());
        }
        return $ret;
    }

    public function connect($remote_address, $remote_port)
    {
        $this->remote_address = $remote_address;
        $this->remote_port    = $remote_port;
        if (!@socket_connect($this->socket, $remote_address, $remote_port)) {
            throw new \Exception("Could not connect to {$remote_address} - {$remote_port}: ".$this->getError());
        }
    }

    public function listen($backlog = 128)
    {
        if (!@socket_listen($this->socket, $backlog)) {
            throw new \Exception(
                "Could not listen to {$this->bind_address} - {$this->bind_port}: "
                .$this->getError()
            );
        }
    }

    public function accept()
    {
        if (($client = socket_accept($this->socket)) === false) {
            throw new \Exception(
                "Could not accept connection to {$this->bind_address} - {$this->bind_port}: "
                .$this->getError()
            );
        }
        return $client;
    }

    public function setNonBlock()
    {
        if (!@socket_set_nonblock($this->socket)) {
            throw new \Exception("Could not set socket non_block: ".$this->getError());
        }
    }

    public function setBlock()
    {
        if (!@socket_setBlock($this->socket)) {
            throw new \Exception("Could not set socket non_block: ".$this->getError());
        }
    }

    public function setReceiveTimeout($sec, $usec)
    {
        if (!@socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $sec, "usec" => $usec))) {
            throw new \Exception("Could not set socket recieve timeout: ".$this->getError());
        }
    }

    public function setReuseAddress($reuse = true)
    {
        $reuse = $reuse ? 1 : 0;
        if (!@socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, $reuse)) {
            throw new \Exception("Could not set SO_REUSEADDR to '$reuse': ".$this->getError());
        }
    }
}
