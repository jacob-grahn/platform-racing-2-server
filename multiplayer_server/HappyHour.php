<?php

class HappyHour {

	private static $active_until = 0;

  public static function activate ($duration = 3600) {
    $time = time();
    if (self::$active_until < $time) {
      self::$active_until = $time;
    }
    self::$active_until += $duration;
  }

  public static function isActive () {
    return self::$active_until >= time();
  }
}

?>
