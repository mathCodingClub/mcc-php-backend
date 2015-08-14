<?php

namespace mcc\obj\templates;

class mobileAngularUI {

  static public $sa;

  static public function generateIndex($sa) {
    self::$sa = json_decode(file_get_contents($sa),true);
    self::setServerDependentParameters();
    print "<!DOCTYPE html>\n<html><head>";    
    self::embed(__DIR__ . '/mobileAngularUI/meta.html');
    print '<script> var CONFIG = {' .
        ' nav: ' . json_encode(self::$sa['nav'], JSON_PRETTY_PRINT) .
        ', sideBarLeftBottomImage: ' . json_encode(self::$sa['sideBarLeftBottomImage'], JSON_PRETTY_PRINT) .
        ', logoTitle: ' . json_encode(self::$sa['logoTitle'], JSON_PRETTY_PRINT) .
        ' };</script>' . PHP_EOL . PHP_EOL;    
    new html5include(getcwd() . '/' . self::$sa['include']);
    if (array_key_exists('headTemplateUrl', self::$sa)) {
      print file_get_contents(getcwd() . '/' . self::$sa['headTemplateUrl']);
    }
    print "\n</head>\n";
    print '<body ng-app="angularJsApp" ng-controller="app">' . PHP_EOL;
    print '<toaster-container toaster-options="{\'time-out\': 3000,\'spinner\':false}"></toaster-container>';
    self::embed(__DIR__ . '/mobileAngularUI/sideBars.html');
    print '<div class="app">';
    self::embed(__DIR__ . '/mobileAngularUI/topBar.html', self::$sa['topBar']);
    if (array_key_exists('bottomBarTemplateUrl', self::$sa)) {            
      self::embed(__DIR__ . '/mobileAngularUI/bottomBarTemplate.html');
      print PHP_EOL . PHP_EOL . PHP_EOL;
    }
    self::embed(__DIR__ . '/mobileAngularUI/appBody.html');
    print '</div>';
    if (array_key_exists('ga', self::$sa)) {
      self::embed(__DIR__ . '/mobileAngularUI/ga.html', self::$sa['ga']);
    }
    print '</body></html>';
  }

  static private function embed($file, $sa = null) {    
    $text = file_get_contents($file);    
    if (is_null($sa)) {
      $sa = self::$sa;
    }
    while (true) {
      preg_match('@({{)([a-zA-Z]*)(}})@', $text, $ar);      
      if (count($ar) == 0) {
        break;
      }
      $text = str_replace($ar[1] . $ar[2] . $ar[3], $sa[$ar[2]], $text);
    }    
    print $text;
  }

  static private function setServerDependentParameters() {
    $server = array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'cli';
    // includes
    if (array_key_exists('includes', self::$sa)) {
      $str = null;
      foreach (self::$sa['includes'] as $key => $value) {
        if ($key == 'default') {
          continue;
        }
        if (preg_match($key, $server)) {
          $str = $value;
          break;
        }
      }
      if (is_null($str)) {
        $str = self::$sa['includes']['default'];
      }
      self::$sa['include'] = $str;
    }
    //
    if (array_key_exists('bases', self::$sa)) {
      $str = null;
      foreach (self::$sa['bases'] as $key => $value) {
        if ($key == 'default') {
          continue;
        }
        if (preg_match($key, $server)) {
          $str = $value;
          break;
        }
      }
      if (is_null($str)) {
        $str = self::$sa['bases']['default'];
      }
      self::$sa['base'] = $str;
    }
  }

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
