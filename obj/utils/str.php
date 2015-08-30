<?php

namespace mcc\obj\utils;

class str {

  static function normalize($str){
    $replacers = array('ä' => 'a','ö' => 'o','\s' => '-');
    $str = strtolower($str);
    foreach ($replacers as $key => $value){
      $str = preg_replace('#' . $key . '#',$value,$str);
    }    
    return $str;
  }    

}
