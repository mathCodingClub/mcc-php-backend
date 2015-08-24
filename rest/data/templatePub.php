<?php

namespace mcc\rest\data;

class templatePub extends \mcc\obj\slimClass\service {

  public function get($code) {
    try {
      $data = \mcc\obj\data\services\template::getByCode($code);
    } catch (\Exception $e) {
      print \mcc\obj\templates\mobileAngularUI::codePageMissing($code);
      return;
    }
    $content = $data->getcontent();
    $this->setCT(self::CT_HTML);
    if (\mcc\obj\templates\annotations::hasAnnotation('BLANK', $content)) {
      print $content;
      return;
    }
    $template = \mcc\obj\templates\mobileAngularUI::pageTemplate('<!-- @DATABASE-TEMPLATE -->' .
                    $content);
    print \mcc\obj\templates\annotations::removeComments($template);
  }

}
