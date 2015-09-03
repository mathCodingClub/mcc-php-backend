<?php

namespace mcc\obj\templates;

class mobileAngularUI {

  static public $sa;

  static public function generateIndex($sa) {
    self::$sa = json_decode(file_get_contents($sa), true);
    self::setServerDependentParameters();
    print "<!DOCTYPE html>\n<html><head>";
    print annotations::embed(__DIR__ . '/mobileAngularUI/meta.html', self::$sa);
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
    print '<body ng-app="' . self::$sa['appName'] . '" ng-controller="app">' . PHP_EOL;
    // preload here all the mcc templates
    $fun = function(&$template, &$controller) {
      self::convertTemplate($template, $controller);
    };
    directives::preload($fun);

    print '<toaster-container toaster-options="{\'time-out\': 3000,\'spinner\':false}"></toaster-container>';
    print annotations::embed(__DIR__ . '/mobileAngularUI/sideBars.html', self::$sa);
    print '<div class="app">';
    print annotations::embed(__DIR__ . '/mobileAngularUI/topBar.html', self::$sa['topBar']);
    if (array_key_exists('bottomBarTemplateUrl', self::$sa)) {
      print annotations::embed(__DIR__ . '/mobileAngularUI/bottomBarTemplate.html', self::$sa);
      print PHP_EOL . PHP_EOL . PHP_EOL;
    }
    print annotations::embed(__DIR__ . '/mobileAngularUI/appBody.html', self::$sa);
    print '</div>';
    if (array_key_exists('ga', self::$sa)) {
      print annotations::embed(__DIR__ . '/mobileAngularUI/ga.html', self::$sa['ga']);
    }
    print '</body></html>';
  }

  static public function codePageMissing($code) {
    $template = file_get_contents(__DIR__ . '/custom/templateMissing.html');
    $template = str_replace('{{CODE}}', $code, $template);
    return self::pageTemplate($template);
  }

  static public function convertTemplate(&$template, &$controller) {

    // do nothing if blank annotation is included
    $template = annotations::setContainerClasses('list-group-item', $template);
    if (\mcc\obj\templates\annotations::hasAnnotation('BLANK', $template)) {
      return;
    }
    // if there is title, this is regular page            
    if (\mcc\obj\templates\annotations::hasAnnotation('TITLE', $template)) {
      $template = self::pageTemplate($template);
    }
    // controllers
    if ($simple = annotations::getValue('MCC-SIMPLE-TEMPLATE', $template, false)) {
      $restrict = 'AE';
      if ($setRestrict = annotations::getValue('RESTRICT', $template, false)) {
        $restrict = $setRestrict;
      }
      $conf = array('NAME' => $simple, 'RESTRICT' => $restrict, 'UCFNAME' => ucfirst($simple));
      $controller = annotations::embed(__DIR__ . '/custom/mccSimpleTemplate.js', $conf);
    }
    if ($directive = annotations::getValue('MCC-DIRECTIVE', $template, false)) {
      $restrict = 'AE';
      if ($setRestrict = annotations::getValue('RESTRICT', $template, false)) {
        $restrict = $setRestrict;
      }
      $conf = array('NAME' => $directive, 'RESTRICT' => $restrict, 'UCFNAME' => ucfirst($directive));
      // create Scope string
      $mapValues = explode(',',annotations::getValue('MAP', $template));
      $scopeArray = array();
      foreach ($mapValues as $key){
        array_push($scopeArray,$key . ": '=$key'");
      }
      $conf['SCOPE-MAP'] = '{' . implode(",\n",$scopeArray) . '}';      
      $controller = annotations::embed(__DIR__ . '/custom/mccDirectiveScopeMap.js', $conf);      
    }
  }

  static public function pageTemplate($template) {
    $top = '';
    $template = annotations::setContainerClasses('list-group-item', $template);
    if ($isolatedScope = annotations::hasAnnotation('ISOLATED-SCOPE', $template)) {
      $top .= '<div mcc-isolated-scope>';
    }
    if ($controller = annotations::getValue('CONTROLLER', $template, false)) {
      $top .= '<div ng-controller="' . $controller . '">';
    }
    if ($ngInit = annotations::getValue('NG-INIT', $template, false)) {
      $top .= '<div ng-init="' . preg_replace('#\r|\n#', '', $ngInit) . '">';
    }
    $top .= '<div class="scrollable scrollable-content">' .
            '<div class="list-group">' .
            '<div class="list-group-item">' .
            '<h1>' . annotations::getValue('TITLE', $template);
    // add logo
    if ($logo = annotations::getValue('LOGO', $template, false)) {
      $top .= '<i class="pull-right fa ' . $logo . ' feature-icon text-primary"></i>';
    }
    // add subtitle
    if ($subtitle = annotations::getValue('SUBTITLE', $template, false)) {
      $top .= '<br><small>' . $subtitle . '</small>';
    }
    $top .= '</h1></div>';
    // code editor button
    if ($codeEditor = annotations::hasAnnotation('DATABASE-TEMPLATE', $template)) {
      $top .= '<div class="list-group-item" ng-show="$root.isLoggedIn">' .
              '<button class="btn btn-primary" ng-click="showCodeEditor()">{{\'MODIFY_TEMPLATE\'| translate}}</button> ' .
              '<button class="btn btn-danger" ng-click="refresh()">{{\'REFRESH\'| translate}}</button>' .
              '</div>';
    }
    // loading spinner
    if ($loadingSpinner = annotations::getValue('LOADING-SPINNER', $template, false)) {
      $top .='<div ng-hide="' . $loadingSpinner . '"' .
              ' class="manual-loading scrollable scrollable-content"' .
              ' style="background-color: white;">' .
              ' <i class="fa fa-spinner fa-spin loading-spinner"></i></div>';
    }
    // container
    if ($hasContainer = !annotations::hasAnnotation('NO-CONTAINER', $template)) {
      $top .= '<div class="list-group-item"';
      if ($loadingSpinner !== false) {
        $top .= ' ng-show="' . $loadingSpinner . '"';
      }
      $top .= '>';
    }
    // bottom
    $bottom = '';
    if ($hasContainer) {
      $bottom .= '</div>';
    }
    $bottom .= '</div></div>';
    if ($ngInit) {
      $bottom .= '</div>';
    }
    if ($controller) {
      $bottom .= '</div>';
    }
    if ($codeEditor) {
      $bottom .= '<div overlay="mcc.overlayEditorData">' .
              '<div mcc-editor-data overlay-data="dataObject"></div></div>';
    }
    if ($isolatedScope) {
      $bottom .= '</div>';
    }    
    return $top . $template . $bottom;
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

}
