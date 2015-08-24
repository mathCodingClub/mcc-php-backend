<?php

namespace mcc\rest;

class index {

  static $services = null;

  static public function config($conf = null) {
    if (is_null($conf)) {
      $conf = 'DICT|FILES|LOGIN|NEWS|TEMPLATES|USER_MANAGEMENT';
    }
    self::$services = $conf;
  }

  public function __construct($path = '/mcc') {

    if (is_null(self::$services)) {
      self::config();
    }

    if ($this->initService('DICT')) {
      new \mcc\rest\dictionary\pub("$path/dict");
    }

    if ($this->initService('FILES')) {
      new \mcc\rest\file\pri("$path/private/files");
    }

    if ($this->initService('LOGIN')) {
      new \mcc\rest\user\auth("$path/private/auth");
    }

    if ($this->initService('USER_MANAGEMENT')) {
      new \mcc\rest\user\admin("$path/root/users");
    }

    if ($this->initService('NEWS')) {
      new \mcc\rest\news\pri("$path/private/news");
      new \mcc\rest\news\pub("$path/news");
    }

    if ($this->initService('TEMPLATES')) {
      new \mcc\rest\data\templatePub("$path/templates");
      new \mcc\rest\data\templatePri("$path/private/templates");
    }
  }

  private function initService($service) {    
    return preg_match("#$service#", self::$services);
  }

}
