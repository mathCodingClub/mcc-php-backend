<?php

namespace mcc\obj\file\services;

class file {

  static private $config;

  static public function config($config) {
    self::$config = $config;
  }

}
