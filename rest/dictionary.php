<?php

namespace mcc\rest;

class dictionary extends \mcc\obj\slimClass\service  {

  private $dict;
  private $config;

  public function __construct($path, $config = array()) {    
    parent::__construct($path);
    $this->config = $config;
  }

  public function middleware() {
    // init parameters from config, glob for dictionaries to be included too
    $this->dict = array_key_exists('dict', $this->config) ? $this->config['dict'] : null;
  }

  public function getJson() {
    // this is made compatible with angular-translate now    
    $lang = $_REQUEST['lang'];
    if (strlen($lang) != 5) {
      throw new \Exception("LANGUAGE_FILE_NOT_FOUND", 404);
    }
    // load here all matching mcc dict
    $mccDict = glob(__DIR__ . "/dictionary/*/$lang.json");
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
