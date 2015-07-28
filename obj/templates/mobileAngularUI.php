<?php

namespace mcc\obj\templates;

class mobileAngularUI {

  static public function contentHeader($title, $logo, $subtitle, $hasContainer, $codeEditorButton = false, $controller = null, $loadingSpinner = false, $initialVars = false) {
    $top = '';
    if (!is_null($controller)) {
      $top .= '<div ng-controller="' . $controller . '">';
    }
    $top .= '
<div class="scrollable"';
    if ($initialVars !== false) {
      $top .= ' ng-init="' . $initialVars . '"';
    }
    $top .= '>
<div class="scrollable-content">
<div class="list-group">
<div class="list-group-item">
<h1>' . $title . '
<div class="pull-right">
<i class="fa ' . $logo . ' feature-icon text-primary"></i>
</div>';
    if (!is_null($subtitle)) {
      $top .= "<br><small>$subtitle</small>";
    }
    $top .= '</h1></div>';
    if ($codeEditorButton) {
      $top .= self::codeEditorButton();
    }
    if ($loadingSpinner !== false) {
      $top .='
<div ng-hide="' . $loadingSpinner . '" 
  class="manual-loading scrollable scrollable-content" 
  style="background-color: white;">
<i class="fa fa-spinner fa-spin loading-spinner"></i>
</div>';
    }
    if ($hasContainer) {
      $top .= '<div class="list-group-item"';
      if ($loadingSpinner !== false) {
        $top .= ' ng-show="' . $loadingSpinner . '"';
      }
      $top .= '>';
    }
    return $top . PHP_EOL . PHP_EOL;
  }

  static public function contentFooter($hasContainer = true, $hasController = false) {
    $bottom = '';
    if ($hasContainer) {
      $bottom .= '</div>';
    }
    $bottom .= '
</div>
</div>           
</div>';
    if ($hasController) {
      $bottom .= '</div>';
    }
    return $bottom . PHP_EOL . PHP_EOL;
  }

  static public function codeEditorButton() {
    $text = '
<div class="list-group-item" ng-show="$root.isLoggedIn">      
<button class="btn btn-primary" ng-click="showCodeEditor()">{{\'MODIFY_TEMPLATE\'| translate}}</button>
</div>';
    return $text;
  }

  static public function codeEditor() {
    $text = '
<div overlay="mcc.overlayEditorData">
<div mcc-editor-data
overlay-data="dataObject">
</div>
</div>';
    return $text;
  }

}
