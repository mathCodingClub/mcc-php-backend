<?php

namespace mcc\obj\templates;

class directives {

  static public function preload($translator = null, $prefix = 'mcc.', $path = null) {

    if (is_null($path)){
      $path = __DIR__ . '/directives/*.html';
    }
    
    $files = glob($path);
    foreach ($files as $file) {
      preg_match('@/([a-zA-Z0-9.]*)(.html)@', $file, $temp);
      $dir = $temp[1];
      $template = file_get_contents($file);
      $controller = null;
      if (!is_null($translator)) {        
        $translator($template,$controller);        
      }
      if (!is_null($controller)) {
        print '<script>' . PHP_EOL;
        print $controller;
        print '</script>' . PHP_EOL;
      }

      print '<script type="text/ng-template" id="' . $prefix . $dir . '">' . PHP_EOL;
      print annotations::removeComments($template);
      print '</script>' . PHP_EOL;
    }
  }
}
