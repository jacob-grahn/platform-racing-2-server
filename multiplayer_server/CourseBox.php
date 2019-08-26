<?php

namespace pr2\multi;

class CourseBox
{

    public $slot_array = array();
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

    public function fillSlot($player, int $slot)
    {
        if ($slot < 0 || $slot > 3) {
            return;
        }
        if (!isset($this->slot_array[$slot])) {
            if (isset($player->course_box)) {
                $player->course_box->clearSlot($player);
            }
            $player->confirmed = false;
            $player->slot = $slot;
            $player->course_box = $this;
            $this->slot_array[$slot] = $player;
            if (empty($this->room)) {
                $this->room = $player->right_room;
                output('room exception! dumping below... $player->user_id: ' . $player->user_id);
                var_dump($this);
            }
            $this->room->sendToRoom($this->getFillStr($player, $slot), $player->user_id);
            $player->write($this->getFillStr($player, $slot, true));

            if (isset($this->force_time)) {
                $force_time = time() - $this->force_time;
                $player->write("forceTime`$force_time");
            }
        }
    }

    public function confirmSlot($player)
    {
        if ($player->confirmed == false) {
            $player->confirmed = true;
            $this->room->sendToAll($this->getConfirmStr($player->slot));
        }

        if (!isset($this->force_time)) {
            $this->force_time = time();
            $this->sendToAll('forceTime`0');
        }

        $this->checkConfirmed();
    }

    public function clearSlot($player)
    {
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

        if (count($this->slot_array) <= 0) {
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
        $course_id = substr($this->course_id, 0, strpos($this->course_id, '_'));
        $game = new Game($course_id);
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
        $this->remove();
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

    public function remove()
    {
        $this->room->course_array[$this->course_id] = null;
        unset($this->room->course_array[$this->course_id]);

        $this->room->maybeHighlight('remove', $this->page_number);

        foreach ($this->slot_array as $player) {
            $this->clearSlot($player);
        }

        $this->slot_array = null;
        unset($this->slot_array);

        // delete the rest
        foreach ($this as $key => $var) {
            $this->$key = null;
            unset($this->$key, $key, $var);
        }
    }
}
