<?php

namespace mcc\rest\templates;

class pub extends \mcc\obj\slimClass\service {

  public function getCodepagecontainer($code) {
    $this->setCT(self::CT_HTML);
    print '<div mcc-code-page="' . $code . '"></div>';
  }

  public function getCodepages($code) {
    
    $data = \mcc\obj\data\services\data::getByCode($code);
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
  
  public function getDirectives($dir){
    $this->setCT(self::CT_HTML);
    print file_get_contents(__DIR__ . '/directives/' . $dir . '.html');
  }

}
