<?php

namespace mcc\obj\repo;

abstract class repodb {

  const ALL = 'INCLUDE_ALL';

  static public function getDB() {
    return \mcc\obj\sql::get();
  }

  protected function getDatabaseVariables() {
    $refl = new \ReflectionClass($this);
    $objs = $refl->getProperties(\ReflectionProperty::IS_PROTECTED);
    $vars = array();
    foreach ($objs as $obj) {
      $name = $obj->getName();
      if (preg_match('@_$@', $name)) {
        array_push($vars, $name);
      }
    }
    return $vars;
  }
  
  protected function getNamespace(){
    $name = get_class($this);    
    preg_match('@^(.*?)[a-zA-Z]*$@',$name,$test);
    return $test[1];    
  }

  protected function getClass(){
    $name = get_class($this);
    preg_match('@[a-zA-Z]*$@',$name,$test);
    return $test[0];
  }
  
}
