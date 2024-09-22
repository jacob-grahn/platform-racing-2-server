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

abstract class SocketClient extends Socket
{
    public $remote_address = null;
    public $remote_port    = null;
    public $connecting     = false;
    public $disconnected   = false;
    public $read_buffer    = '';
    public $write_buffer   = '';

    public function connect($remote_address, $remote_port)
    {
        $this->connecting = true;
        try {
            parent::connect($remote_address, $remote_port);
        } catch (\Exception $e) {
            echo "Caught exception: ".$e->getMessage()."\n";
        }
    }

    public function write($buffer, $length = 4096)
    {
        $this->write_buffer .= $buffer;
        $this->doWrite();
    }

    public function doWrite()
    {
        $length = strlen($this->write_buffer);
        try {
            $written = parent::write($this->write_buffer, $length);
            if ($written < $length) {
                $this->write_buffer = substr($this->write_buffer, $written);
            } else {
                $this->write_buffer = '';
            }
            $this->onWrite();
            return true;
        } catch (\Exception $e) {
            $old_socket         = (string)$this->socket;
            $this->close();
            $this->socket       = $old_socket;
            $this->disconnected = true;
            $this->onDisconnect();
            return false;
        }
        return false;
    }

    public function read($length = 4096)
    {
        try {
            $this->read_buffer .= parent::read($length);
            $this->onRead();
        } catch (\Exception $e) {
            $old_socket         = (string)$this->socket;
            $this->close();
            $this->socket       = $old_socket;
            $this->disconnected = true;
            $this->onDisconnect();
        }
    }

    public function onConnect()
    {
    }
    public function onDisconnect()
    {
    }
    public function onRead()
    {
    }
    public function onWrite()
    {
    }
    public function onTimer()
    {
    }
}
