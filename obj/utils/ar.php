<?php

namespace mcc\obj\utils;

class ar {

  static public function convertDatetime2timestamp(&$data, $fields, $microtime = false) {
    foreach ($fields as $key) {
      $data[$key] = time::datetime2timestamp($data[$key]);
      if ($microtime){
        $data[$key] = 1000*$data[$key];
      }
    }
  }

  static public function convertQueryResultToRepoObjects($class,$data){
    $ar = array();
    foreach ($data as $key => $value){
      $obj = new $class();
      $obj->initByData($value);
      $ar[$key] = $obj;
    }
    return $ar;
  }
  
  static public function convertObjectsToArray($arrayOfOjects,$fun){
    $data = array();
    foreach ($arrayOfOjects as $obj){
      array_push($data,$obj->$fun());
    }
    return $data;
  }
  
  static public function populatIfNotNull(&$array, $data, $keys = array()) {
    if (count($keys) == 0) {
      foreach ($data as $key => $value) {
        if (!is_null($value)) {
          $array[$key] = $value;
        }
      }
    } else {
      foreach ($keys as $key) {
        if (array_key_exists($key, $data) && !is_null($data[$key])) {
          $array[$key] = $data[$key];
        }
      }
    }
  }
  
  static public function unsetValues(&$array,$keys){
    foreach ($keys as $key){
      unset($array[$key]);
    }
  }
  
  // sorts objects in associative array by the string length of given field
  static public function sortByStringLengthDesc(&$array,$key){
    
    $sortfun = function($a,$b) use ($key){
      if (strlen($a[$key]) == strlen($b[$key])){
        return 0;
      }
      return strlen($a[$key]) < strlen($b[$key]) ? 1 : -1;            
    };
    usort($array,$sortfun);    
  }

    

}
