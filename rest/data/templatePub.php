<?php

namespace mcc\rest\data;

class templatePub extends \mcc\obj\slimClass\service {

  static public $config;

  static public function config($config) {
    self::$config = $config;
  }

  public function get($code) {
    try {
      $data = \mcc\obj\data\services\template::getByCode($code);
    } catch (\Exception $e) {
      print \mcc\obj\templates\mobileAngularUI::codePageMissing($code);
      return;
    }
    $content = "<!-- @DATABASE-TEMPLATE -->\n" . $data->getcontent();
    $this->produce($content);
  }

  public function getLocal($template) {              
    $template = file_get_contents(self::$config['dir'] . '/' . $template . '.html');    
    $this->produce($template);
  }

  private function produce($content) {
    $this->setCT(self::CT_HTML);    
    if (\mcc\obj\templates\annotations::hasAnnotation('BLANK', $content) ||
            !\mcc\obj\templates\annotations::hasAnnotation('TITLE', $content)) {
      print \mcc\obj\templates\annotations::setContainerClasses('list-group-item', $content);
      return;
    }
    $template = \mcc\obj\templates\mobileAngularUI::pageTemplate($content);
    print \mcc\obj\templates\annotations::removeComments($template);
  }

}
