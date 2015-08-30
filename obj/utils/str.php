<?php

namespace mcc\obj\utils;

class str {

  static public function normalize($str) {
    $replacers = array('ä' => 'a', 'ö' => 'o', '\s' => '-');
    $str = strtolower($str);
    foreach ($replacers as $key => $value) {
      $str = preg_replace('#' . $key . '#', $value, $str);
    }
    return $str;
  }

  // convert subscripts to latex subscripts, italic to italics etc
  static public function html2latex($str) {
    $replacers = array(
        '<sub>([0-9]*)</sub>' => '$_{$1}$',
        '<sub>(.*)</sub>' => '$_{\text{$1}}$',
        '<i>(.*)</i>' => '$$1$');
    foreach ($replacers as $key => $value) {
      $str = preg_replace('#' . $key . '#', $value, $str);
    }
    return $str;
  }

}
