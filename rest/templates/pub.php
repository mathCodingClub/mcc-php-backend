<?php

namespace mcc\rest\templates;

class pub extends \mcc\obj\slimClass\service {

  public function getCodepages($code) {
    try {
      $data = \mcc\obj\data\services\data::getByCode($code);
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
    $template = \mcc\obj\templates\mobileAngularUI::pageTemplate('<!-- @DATA-PAGE -->' .
                    $content);
    print \mcc\obj\templates\annotations::removeComments($template);
  }

}
