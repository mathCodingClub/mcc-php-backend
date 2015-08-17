<?php


namespace mcc\obj\templates;

class directives {
 
  static public function preload() {
    
    
    $files = glob(__DIR__ . '/directives/*.html');    
    foreach ($files as $file){
      preg_match('@/([a-zA-Z0-9.]*)(.html)@',$file,$temp);
      $dir = $temp[1];
      print '<script type="text/ng-template" id="mcc.' . $dir . '">' . PHP_EOL;
      print file_get_contents($file);
      print '</script>' . PHP_EOL;           
    }
    
  }
  
}
