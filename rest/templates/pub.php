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

    $title = \mcc\obj\templates\annotations::getValue('TITLE', $content);
    $subtitle = \mcc\obj\templates\annotations::getValue('SUBTITLE', $content, null);
    $logo = \mcc\obj\templates\annotations::getValue('LOGO', $content, 'fa-bike');
    $hasContainer = !\mcc\obj\templates\annotations::hasAnnotation('NO-CONTAINER', $content);
    $controller = \mcc\obj\templates\annotations::getValue('CONTROLLER', $content);
    $loadingSpinner = \mcc\obj\templates\annotations::getValue('LOADING-SPINNER', $content, false);
    $initialVars = \mcc\obj\templates\annotations::getValue('NG-INIT', $content, false);
    $containerClass = 'list-group-item';

    $content = \mcc\obj\templates\annotations::setContainerClasses($containerClass, $content);
    $content = \mcc\obj\templates\annotations::addContainerClassBreaks($containerClass, $content);
    $content = \mcc\obj\templates\annotations::removeComments($content);

    print \mcc\obj\templates\mobileAngularUI::contentHeader($title, $logo, $subtitle, $hasContainer, true, $controller, $loadingSpinner, $initialVars);
    print $content;
    print \mcc\obj\templates\mobileAngularUI::contentFooter($hasContainer, !is_null($controller));
    print \mcc\obj\templates\mobileAngularUI::codeEditor();
  }

}