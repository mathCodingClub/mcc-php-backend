<?php

namespace mcc\obj\cache;

class cache {

  static public function clearCache(){
    shell_exec('rm -f ' . __DIR__ . '/files/*');    
  }
  
  static public function isOlderThan($key,$sec){        
    // cache garbage collector
    if (rand(1,100) > 95){
      self::clearCache();
    }
    
    if (!file_exists(self::file($key))){
      return true;
    }
    if ((time() - filemtime(self::file($key))) > $sec){
      return true;
    }
    return false;
  }
  
  static public function get($key){    
    if (!file_exists(self::file($key))){
      throw new \mcc\obj\mccException(array('msg' => 'File does not exist.'));
    }
    return unserialize(file_get_contents(self::file($key)));
  }
  
  static public function set($key,$data){
    $content = serialize($data);
    file_put_contents(self::file($key), $content);
  }
  
  static private function file($key){
    return __DIR__ . '/files/' . $key;
  }
  
}
