<?php

namespace mcc\obj\templates;

class annotations {

  static public function getValue($annotation, $string, $default = null) {
    preg_match("#(<!-- @$annotation:)(.*?)(-->)#s", $string, $matches);
    if (count($matches) == 4) {
      return trim($matches[2]);
    }
    return $default;
  }

  static public function hasAnnotation($annotation, $string) {
    return preg_match('@' . $annotation . '[: ]@',$string);
  }

  static public function setContainerClasses($container_class, $string) {
    $string = str_replace('@CONTAINER_CLASS', $container_class, $string);
    return str_replace('@CONTAINER-CLASS', $container_class, $string);
  }

  static public function removeComments($string) {
    while (true) {
      preg_match('#(<!--)(.*?)(-->)#s', $string, $matches);
      if (count($matches) == 4) {
        $string = str_replace($matches[0], '', $string);
        continue;
      }
      break;
    }
    return $string;
  }

  static public function addContainerClassBreaks($container_class, $string) {
    $content_break = '</div><div class="' . $container_class . '">';
    return str_replace('<!-- BREAK -->', $content_break, $string);
  }

}
