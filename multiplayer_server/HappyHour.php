<?php

class HappyHour {

	private static $active_until = 0;
  private static $random_hour = rand(0, 23);

  public static function activate ($duration = 3600) {
    $time = time();
    if (self::$active_until < $time) {
      self::$active_until = $time;
    }
    self::$active_until += $duration;
  }

  public static function isActive () {
    $current_hour = (int) date('G');
    return self::$active_until >= time() || $current_hour === $random_hour;
  }
}

?>
