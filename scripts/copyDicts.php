<?php

$languages = array('fi-FI', 'gb-EN');


$dir = __DIR__ . '/../obj/dictionary';


foreach (scandir($dir) as $file) {
  if ('.' === $file) {
    continue;
  }
  if ('..' === $file) {
    continue;
  }
  $dict = array();
  foreach ($languages as $lang) {
    try {
      $ar = json_decode(file_get_contents("$dir/$file/$lang.json"), true);
      if (!is_array($ar)) {
        throw new Exception('does not exist', 500, null);
      }
      $dict[$lang] = $ar;
    } catch (Exception $ex) {
      $dict[$lang] = array();
    }
  }

  foreach ($dict as $key => $ref) {
    foreach ($dict as $key2 => $comp) {
      if ($key == $key2) {
        continue;
      }
      foreach ($ref as $dictKey => $dictValue) {
        $dict[$key2] = addKeyReg($ref, $dict[$key2], $dictKey, $key2);
      }
    }
  }  
  foreach ($languages as $lang) {
    file_put_contents(__DIR__ . "/../obj/dictionary/$file/$lang.json", json_encode($dict[$lang], JSON_PRETTY_PRINT));
  }
}

function addKeyReg($from, $to, $key, $newLang) {
  if (!is_array($from[$key])) {
    if (!array_key_exists($key, $to)) {
      $to[$key] = '';
    }
    if ($to[$key] == '' && true) {      
      print ' -- ' . $from[$key] . PHP_EOL . ' -- Does not exist (in lang ' . $newLang . ') -- Key: ' . $key . ' -- ' . PHP_EOL;
      $line = fgets(STDIN);
      $to[$key] = trim($line);
    }
    return $to;
  } else {
    if (!array_key_exists($key, $to)) {
      $to[$key] = array();
    }
    foreach ($from[$key] as $key2 => $value) {
      $to[$key][$key2] = addKeyReg($from[$key], $to[$key], $key2, $newLang)[$key2];
    }
    return $to;
  }
}
