<?php

namespace mcc\rest\dictionary;

class pub extends \mcc\obj\slimClass\service  {

  private $dict;
  static private $config;   
  
  static public function config($ar){
    self::$config = $ar;
  }
  
  public function middleware() {  
    $this->dict = (is_array(self::$config) && array_key_exists('dict', self::$config)) ? self::$config['dict'] : null;
  }

  public function getJson() {
    // this is made compatible with angular-translate now    
    $lang = $_REQUEST['lang'];
    if (strlen($lang) != 5) {
      throw new \Exception("LANGUAGE_FILE_NOT_FOUND", 404);
    }
    // load here all matching mcc dict
    $mccDict = glob(__DIR__ . "/../../obj/dictionary/*/$lang.json");
    $dict = array();
    foreach ($mccDict as $file) {      
      $dict = array_merge($dict, json_decode(file_get_contents($file), true));
    }
    if (!is_null($this->dict)) {
      $files = glob($this->dict);
      foreach ($files as $file) {
        $dict = array_merge($dict, json_decode(file_get_contents($file), true));
      }
    }
    if (count($dict) == 0) {
      throw new \Exception("LANGUAGE_FILE_NOT_FOUND", 404);
    }
    $this->sendArrayAsJSON($dict);
  }

  
  public function getAvailableLanguages(){
    $this->sendArrayAsJSON(array(
        array('short' => 'fi-FI','long' => 'Suomi'),
        array('short' => 'gb-EN','long' => 'English')
    ));
  }
}
