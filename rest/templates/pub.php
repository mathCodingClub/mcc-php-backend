<?php

namespace mcc\rest\templates;

class pub extends \mcc\obj\slimClass\service {
 
  public function getCodepages($code) {
    try {
      $data = \mcc\obj\data\services\data::getByCode($code);
    } catch (\Exception $e) {
      $title = '{{"DATA.DOES_NOT_EXIST" | translate}}';
      $subtitle = '';
      $logo = 'fa-cog';      
      print \mcc\obj\templates\mobileAngularUI::contentHeader($title, $logo, $subtitle, true);
      print '<button class="btn btn-primary" ng-show="$root.isLoggedIn" mcc-code-page-create="' . $code . 
          '" ng-click="create()">{{"CREATE" | translate}} \'' . $code . '\'</button>';
      print \mcc\obj\templates\mobileAngularUI::contentFooter(true);
      print \mcc\obj\templates\mobileAngularUI::codeEditor();
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
