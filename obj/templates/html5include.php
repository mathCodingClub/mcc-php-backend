<?php

namespace mcc\obj\templates;

class html5include {

  private $included = array();

  public function __construct($json = 'html5includes.json', $common = array()) {

    // getch json data
    $data = json_decode(file_get_contents($json), true);

    if (!is_array($data)) {
      throw new \Exception('Could not read include.json file');
    }

    // make queries
    if (array_key_exists('css', $data)) {
      $this->inc_css($data['css']);
    }
    if (array_key_exists('js', $data)) {
      $this->inc_js($data['js']);
    }
    foreach ($common as $key) {
      $value = $data['optional'][$key];
      if (array_key_exists('css', $value)) {
        $this->inc_css($value['css']);
      }
      if (array_key_exists('js', $value)) {
        $this->inc_js($value['js']);
      }
    }
  }

  private function inc_css($data) {
    $print = function($val) {
      self::print_css($val);
      //$this::print_css($val);
    };
    $this->inc($data, $print);
  }

  private function inc_js($data) {
    $print = function($val) {
      self::print_js($val);
      //$this::print_js($val);
    };
    self::inc($data, $print);
  }

  private function inc($data, $print_fun) {
    foreach ($data as $pattern) {
      // web resource
      if (strpos($pattern, '//') !== false &&
              !in_array($pattern, $this->included)) {
        $print_fun($pattern);
        array_push($this->included, $pattern);
        continue;
      }
      // local file, could well be multifile
      $files = glob($pattern);
      foreach ($files as $file) {
        if (in_array($file, $this->included)) {
          continue;
        }
        $print_fun($file);
        array_push($this->included, $file);
      }
    }
  }

  static public function print_css($file) {
    print '<link rel="stylesheet" href="' . $file . '" \>' . PHP_EOL;
  }

  static public function print_js($file) {
    print '<script src="' . $file . '"></script>' . PHP_EOL;
  }

}
