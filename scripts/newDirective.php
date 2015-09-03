<?php

$param = count($argv) > 1 ? $argv[1] : '--help';

if ($param == '--help') {
  print file_get_contents(__DIR__ . '/newTemplates/newDirectiveHelp.txt');
  return 0;
}

$root = str_replace('/php/mcc/scripts', '', __DIR__);

require_once "$root/vendor/autoload.php";

$directive = $argv[1];

// configuration
if (in_array('-m', $argv)) {
  $conf = array('pathJs' => "$root/js-mcc/directives/$directive.js",
      'pathHtml' => "$root/php/mcc/obj/templates/directives/$directive.html",
      'templateUrl' => "mcc.$directive",
      'directive' => 'mcc' . ucfirst($directive));
} else {
  $conf = array('pathJs' => "$root/js/directives/$directive.js",
      'pathHtml' => "$root/templates/$directive.html",
      'templateUrl' => "rest/mcc/templates/local/$directive",
      'directive' => $directive);
}

$conf['restrict'] = 'AE';
if (in_array('-r', $argv)) {
  $index = array_search('-r', $argv);
  $conf['restrict'] = $argv[$index + 1];
}

// make documents
if (!in_array('-h', $argv)) { // make also javascript file  
  $text = \mcc\obj\templates\annotations::embed(__DIR__ . '/newTemplates/directive.js', $conf);
  file_put_contents($conf['pathJs'], $text);
  print ".js for directive generate to {$conf['pathJs']}.\n";
}

// make javascripts
if (!in_array('-j', $argv)) { // make also html template
  if (in_array('-t', $argv)) {
    $text = \mcc\obj\templates\annotations::embed(__DIR__ . '/newTemplates/full.html', $conf);
    file_put_contents($conf['pathHtml'], $text);
  } else {
    $text = \mcc\obj\templates\annotations::embed(__DIR__ . '/newTemplates/blank.html', $conf);
    file_put_contents($conf['pathHtml'], $text);
  }
  print ".html for directive generate to {$conf['pathHtml']}.\n";
}