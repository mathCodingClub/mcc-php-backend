<?php

namespace mcc\obj\service;

abstract class base {

  // These can be overridden now easily
  public static function __callStatic($name, $args) {
    if (preg_match('@^deleteById$@', $name)) {
      return self::deleteById_($args[0]);
    } elseif (preg_match('@^save$@', $name)) {
      return self::save_($args[0]);
    } elseif (preg_match('@^create$@', $name)) {
      return self::create_($args[0]);
    } elseif (preg_match('@^get$@', $name)) {
      return self::getById_($args[0]);
    } else {
      throw new \mcc\obj\mccException(array(
      'code' => 404,
      'dict' => 'ERROR_MISUSE',
      'msg' => 'Unkown static method in mcc\obj\service\base. (' . $name . ')'
      ));
    }
  }

  static public function getDB() {
    return \mcc\obj\sql::get();
  }

  static protected function create_($data) {
    $class = static::REPO;
    return new $class($data);
  }

  static protected function deleteById_($id) {
    $class = static::REPO;
    $class::delete($id);
  }

  static protected function getById_($id) {
    $class = static::REPO;
    return new $class($id);
  }
  
  static protected function getBy_($key,$value){
    $class = static::REPO;
    $obj = new $class();
    $fun = 'initBy' . $key;
    $obj->$fun($value);
    return $obj;
  }

  static protected function save_($data) {
    $class = static::REPO;
    $obj = new $class($data['id']);
    $obj->set($data);
    return $obj;
  }

}
