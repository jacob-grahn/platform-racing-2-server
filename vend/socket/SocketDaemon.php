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

class SocketDaemon
{
    public $servers = array();
    public $clients = array();

    public function createServer($server_class, $client_class, $bind_address = 0, $bind_port = 0)
    {
        $server = new $server_class($client_class, $bind_address, $bind_port);
        if (!is_subclass_of($server, '\chabot\SocketServer')) {
            throw new \Exception("Invalid server class specified! Has to be a subclass of \chabot\SocketServer");
        }
        $this->servers[(int)$server->socket] = $server;
        return $server;
    }

    public function createClient($client_class, $remote_address, $remote_port, $bind_address = 0, $bind_port = 0)
    {
        $client = new $client_class($bind_address, $bind_port);
        if (!is_subclass_of($client, '\chabot\SocketClient')) {
            throw new \Exception("Invalid client class specified! Has to be a subclass of \chabot\SocketClient");
        }
        $client->setNonBlock(true);
        $client->connect($remote_address, $remote_port);
        $this->clients[(int)$client->socket] = $client;
        return $client;
    }

    private function createReadSet()
    {
        $ret = array();
        foreach ($this->clients as $socket) {
            $ret[] = $socket->socket;
        }
        foreach ($this->servers as $socket) {
            $ret[] = $socket->socket;
        }
        return $ret;
    }

    private function createWriteSet()
    {
        $ret = array();
        foreach ($this->clients as $socket) {
            if (!empty($socket->write_buffer) || $socket->connecting) {
                $ret[] = $socket->socket;
            }
        }
        foreach ($this->servers as $socket) {
            if (!empty($socket->write_buffer)) {
                $ret[] = $socket->socket;
            }
        }
        return $ret;
    }

    private function createExceptionSet()
    {
        $ret = array();
        foreach ($this->clients as $socket) {
            $ret[] = $socket->socket;
        }
        foreach ($this->servers as $socket) {
            $ret[] = $socket->socket;
        }
        return $ret;
    }

    private function cleanSockets()
    {
        foreach ($this->clients as $socket) {
            if ($socket->disconnected || !$this->socket instanceof \Socket) {
                if (isset($this->clients[(int)$socket->socket])) {
                    unset($this->clients[(int)$socket->socket]);
                }
            }
        }
    }

    private function getClass($socket)
    {
        if (isset($this->clients[(int)$socket])) {
            return $this->clients[(int)$socket];
        } elseif (isset($this->servers[(int)$socket])) {
            return $this->servers[(int)$socket];
        } else {
            throw (new \Exception("Could not locate socket class for $socket"));
        }
    }

    public function process()
    {
        // if SocketClient is in write set, and $socket->connecting === true, set connecting to false and call onConnect
        $read_set      = $this->createReadSet();
        $write_set     = $this->createWriteSet();
        $exception_set = $this->createExceptionSet();
        $event_time    = time();
        while (($events = socket_select($read_set, $write_set, $exception_set, 2)) !== false) {
            if ($events > 0) {
                foreach ($read_set as $socket) {
                    $socket = $this->getClass($socket);
                    if (is_subclass_of($socket, '\chabot\SocketServer')) {
                        $client = $socket->accept();
                        $this->clients[(int)$client->socket] = $client;
                    } elseif (is_subclass_of($socket, '\chabot\SocketClient')) {
                        // regular onRead event
                        $socket->read();
                    }
                }
                foreach ($write_set as $socket) {
                    $socket = $this->getClass($socket);
                    if (is_subclass_of($socket, '\chabot\SocketClient')) {
                        if ($socket->connecting === true) {
                            $socket->onConnect();
                            $socket->connecting = false;
                        }
                        $socket->doWrite();
                    }
                }
                foreach ($exception_set as $socket) {
                    $socket = $this->getClass($socket);
                    if (is_subclass_of($socket, '\chabot\SocketClient')) {
                        $socket->onDisconnect();
                        if (isset($this->clients[(int)$socket->socket])) {
                            unset($this->clients[(int)$socket->socket]);
                        }
                    }
                }
            }
            if (time() - $event_time > 2) {
                // only do this if more then a second passed, else we'd keep looping this for every bit recieved
                foreach ($this->clients as $socket) {
                    $socket->onTimer();
                }
                foreach($this->servers as $server) {
					$server->onTimer();
				}
                $event_time = time();
            }
            $this->cleanSockets();
            $read_set      = $this->createReadSet();
            $write_set     = $this->createWriteSet();
            $exception_set = $this->createExceptionSet();
        }
    }
}
