<?php

namespace mcc\obj\utils;

class time {

  static public function datetime2timestamp($datetime) {
    $year = substr($datetime, 0, 4);
    $month = substr($datetime, 5, 2);
    $day = substr($datetime, 8, 2);
    $hour = substr($datetime, 11, 2);
    $min = substr($datetime, 14, 2);
    $sec = substr($datetime, 17, 2);
    try {
      $timestamp = mktime($hour, $min, $sec, $month, $day, $year);
    } catch (\Exception $e) {
      return 0;
    }
    return $timestamp;
  }

  static public function ddmmyyyy2timestamp($ddmmyyyy) {
    $year = substr($ddmmyyyy, 6, 4);
    $month = substr($ddmmyyyy, 3, 2);
    $day = substr($ddmmyyyy, 0, 2);
    return self::datetime2timestamp($year . '-' . $month . '-' . $day . ' 12:00:00');
  }

}
