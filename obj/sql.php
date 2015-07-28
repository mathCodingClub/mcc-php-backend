<?php

namespace mcc\obj;

class sql {

  static $USERNAME = null;
  static $PASSWORD = null;
  static $DATABASE = null;

  static public function init($database, $username, $password) {
    self::$DATABASE = $database;
    self::$USERNAME = $username;
    self::$PASSWORD = $password;
  }

  static public function get() {
    return new \mcc\obj\db\sql\sql(self::$DATABASE, self::$USERNAME, self::$PASSWORD);
  }

}
