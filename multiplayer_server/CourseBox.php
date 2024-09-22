<?php

namespace pr2\multi;

class CourseBox
{

    public $slot_array = array();
    public $starting = false;
    public $room;
    public $course_id;
    public $page_number;
    private $force_time;

    public function __construct($room, $course_id, $page_number = 0)
    {
        $this->room = $room;
        $this->course_id = $course_id;
        $this->page_number = (int) $page_number;

        $this->room->maybeHighlight('add', $this->page_number);
        $this->room->course_array[$this->course_id] = $this;
    }


    private function ensureRoom($room)
    {
        if (empty($this->room) && !empty($room)) {
            $this->room = $room;
        } elseif (empty($this->room) && empty($room)) {
            throw new \Exception("Exception encountered on ensureRoom.");
        }
    }


    public function fillSlot($room, $player, int $slot)
    {
        // back up data, just in case...
        $page_number = $this->page_number;
        $course_id = $this->course_id;

        // sanity check (slot to fill?)
        if ($slot < 0 || $slot > 3) {
            return;
        }

        // add player to slot array
        if (!isset($this->slot_array[$slot])) {
            if (isset($player->course_box)) {
                $player->course_box->clearSlot($player);
            }
            $player->confirmed = false;
            $player->slot = $slot;
            $player->course_box = $this;
            $this->slot_array[$slot] = $player;

            // restore data
            $this->room = $player->right_room = $room;
            $this->page_number = $page_number;
            $this->course_id = $course_id;

            // notify players
            $room->sendToRoom($this->getFillStr($player, $slot), $player->user_id);
            $player->write($this->getFillStr($player, $slot, true));

            if (isset($this->force_time)) {
                $force_time = time() - $this->force_time;
                $player->write("forceTime`$force_time");
            }
        }
    }

    public function confirmSlot($player)
    {
        // notify everyone that this is no joke
        if ($player->confirmed == false) {
            $player->confirmed = true;
            $this->room->sendToAll($this->getConfirmStr($player->slot));
        }

        // initiate the countdown if it hasn't already started
        if (!isset($this->force_time)) {
            $this->force_time = time();
            $this->sendToAll('forceTime`0');
        }

        $this->checkConfirmed();
    }

    public function clearSlot($player, $recursed = false)
    {
        // sanity check (what room am I in? who am I? where am I going?)
        try {
            $this->ensureRoom(@$player->right_room);
        } catch (\Exception $e) {
            output('Exception from: confirmSlot');
            $this->remove(true);
            return;
        }

        $slot = $player->slot;

        $player->confirmed = false;
        $player->slot = null;
        $player->course_box = null;

        $this->slot_array[$slot] = null;
        unset($this->slot_array[$slot]);
        $this->room->sendToAll($this->getClearStr($slot));

        if ($this->countConfirmed() <= 0) {
            $this->force_time = null;
            $this->sendToAll('forceTime`-1');
        }

        if (count($this->slot_array) <= 0 && $recursed === false) {
            $this->remove();
        } else {
            $this->checkConfirmed();
        }
    }

    public function catchUp($to_player)
    {
        foreach ($this->slot_array as $player) {
            $to_player->write($this->getFillStr($player, $player->slot));
            if ($player->confirmed) {
                $to_player->write($this->getConfirmStr($player->slot));
            }
        }
    }

    private function getFillStr($player, $slot, $is_me = false)
    {
        $me = $is_me ? 'me' : '';
        return "fillSlot$this->course_id`$slot`$player->name`$player->active_rank`$me";
    }

    private function getConfirmStr($slot)
    {
        return "confirmSlot$this->course_id`$slot";
    }

    private function getClearStr($slot)
    {
        return "clearSlot$this->course_id`$slot";
    }

    private function checkConfirmed()
    {
        $all_confirmed = true;
        foreach ($this->slot_array as $player) {
            if (!$player->confirmed) {
                $all_confirmed = false;
                break;
            }
        }
        if ($all_confirmed) {
            $this->startGame();
        }
    }

    private function startGame()
    {
        $this->starting = true;
        $course_id = substr($this->course_id, 0, strpos($this->course_id, '_'));
        $game = new Game($course_id, $this->room->getType());
        foreach ($this->slot_array as $player) {
            if ($player->active_rank < 100 || $player->user_id === FRED) {
                $player->confirmed = false;
                $game->addPlayer($player);
                client_set_right_room($player->socket, 'none');
                client_set_chat_room($player->socket, 'none');
            } else {
                $slot = $player->slot; // get slot
                $player->write('message`Some data was incorrect. Please log in again.');
                $player->remove(); // disconnect them
                $this->slot_array[$slot] = null; // clear that slot
                unset($this->slot_array[$slot]); // ^
            }
        }
        $game->init();
        $this->remove(false, true);
    }

    public function forceStart()
    {
        if ((time() - $this->force_time) > 15) {
            foreach ($this->slot_array as $player) {
                if (!$player->confirmed) {
                    $this->clearSlot($player);
                    $player->write('closeCourseMenu`');
                }
            }
        }
    }

    private function sendToAll($str)
    {
        foreach ($this->slot_array as $player) {
            $player->socket->write($str);
        }
    }

    public function sendToRoom($str, $from_id)
    {
        foreach ($this->slot_array as $player) {
            if ($player->user_id != $from_id) {
                $player->socket->write($str);
            }
        }
    }

    private function countConfirmed()
    {
        $num = 0;
        foreach ($this->slot_array as $player) {
            if ($player->confirmed) {
                $num++;
            }
        }
        return $num;
    }

    public function remove($fromE = false, $fromGame = false)
    {
        // backup (this environment is being weird)
        $course_id = $this->course_id;
        $room = $this->room;
        $page_number = $this->page_number;

        if ($this->starting === true && $fromGame === false) {
            return;
        }

        foreach ($this->slot_array as $player) {
            $this->clearSlot($player, true);
        }

        unset($this->slot_array);

        if (!empty($room)) {
            unset($this->room->course_array[$course_id]);
        }

        if (!empty($room) && $fromE === false) {
            $room->maybeHighlight('remove', $page_number);
        }

        // delete the rest
        $course_id = $room = $page_number = null;
        unset($course_id, $room, $page_number);
        foreach ($this as $key => $var) {
            $this->$key = null;
            unset($this->$key, $key, $var);
        }
    }
}
