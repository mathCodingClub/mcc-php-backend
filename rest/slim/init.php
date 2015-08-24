<?php

namespace mcc\rest\slim;

class init {

  static public $debugLive = false;
  static public $debugDev = true;
  static public $devServerName = 'localhost';
  private $app;

  static public function setDebug($dev, $live = false) {
    self::$debugDev = $dev;
    self::$debugLive = $live;
  }

  public function __construct() {
    if (!$this->getDebugLevel()) {
      // no debug
      $app = new \Slim\Slim(array('debug' => false));
      $app->error(function(\Exception $e) use ($app) {
        $msg = $e->getMessage();
        $app->contentType('application/json;charset=utf-8');
        $code = 500;
        if (is_a($e, '\mcc\obj\mccException')) {
          $dict = $e->getDictionaryCode();
          $data = array('dict' => $dict,
              'msg' => $msg,
              'params' => $e->getParams());
          $code = $e->getCode();
        } else {
          $dict = "ERROR_UNKNOWN";
          $data = array('dict' => $dict,
              'msg' => 'Unknown error. Perhaps you need to debug.');
        }
        $app->halt($code, json_encode($data, JSON_NUMERIC_CHECK));
      });
    } else {
      // debug
      $app = new \Slim\Slim(array('debug' => true));
    }
    $this->app = $app;
  }

  public function run() {    
    $this->app->run();
  }

  private function getDebugLevel() {
    return $_SERVER['SERVER_NAME'] == self::$devServerName ? self::$debugDev : self::$debugLive;
  }

}
