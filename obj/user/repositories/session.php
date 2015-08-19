<?php

namespace mcc\obj\user\repositories;

class session extends \mcc\obj\repo\repobase {

  protected $user_id_;
  protected $session_;
  protected $device_;

  /**
   * @type: timestamp
   */
  protected $time_;

  // constant
  const TABLE = 'sessions';
  
  static public function createFor($user) {
    $hash = hash('ripemd160', uniqid() . $user->getusername());
    $data = array('user_id' => $user->getid(), 'session' => $hash);
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $data['device'] = $_SERVER['HTTP_USER_AGENT'];
    }
    $session = new self($data);    
    $user->setSession($session);
    return $session;
  }

}
