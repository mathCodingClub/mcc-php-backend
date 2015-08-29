<?php

namespace mcc\obj\slimClass;

class annotationReader {

  // $method is ReflectionMethod
  static public function getRoute($method) {
    $doc = $method->getDocComment();
    return self::getAnnotationValue($doc, 'route');
  }
  
  static public function getMiddleware($method) {
    $doc = $method->getDocComment();
    return self::getAnnotationValue($doc, 'middleware');
  }

  static private function getAnnotationValue($docComment, $annotation, $default = null) {
    if (strlen($docComment) == 0){
      return $default;
    }
    preg_match('#(@' . $annotation . ':)(.*?)\n#', $docComment, $ann);        
    if (count($ann) == 3) {      
      return trim($ann[2]);
    }
    return $default;
  }

}
