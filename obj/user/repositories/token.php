<?php

namespace mcc\obj\user\repositories;

class token extends \mcc\obj\repo\repobase {

  protected $user_id_;
  protected $token_;

  /**
   * @type: timestamp
   */
  protected $time_;

  // constant
  const TABLE = 'tokens';

  static public function createFor($user) {
    $hash = hash('ripemd160', uniqid() . $user->getUsername());
    $data = array('user_id' => $user->getId(), 'token' => $hash);
    return new self($data);
  }

  static public function getUserFor($tokenStr) {
    $db = self::getDB();
    try {
      $data = $db->getById(static::TABLE, $tokenStr, 'token', '*', false, 'order by time desc limit 0,1');
      $token = new self();
      $token->initByData($data);
    } catch (\Exception $e) {
      self::tokenFailed('Does not exist.');
    }    
    if ($token->getTime() < ceil(1000*(time() - 60))) {
      error_log('Veikko');
      error_log('TIME ' . time() . ':' . $token-getTime());
      self::tokenFailed('Too old.');
    }
    $user = new user($token->getUser_id());
    $db->deleteById(static::TABLE, $user->getId(), 'user_id');
    return $user;
  }  

  static private function tokenFailed($reason) {    
    throw new \mcc\obj\mccException(array('msg' => "Token auth failed. ($reason)",
    'code' => 404,
    'dict' => 'TOKEN_AUTH_FAILED'));
  }

}
